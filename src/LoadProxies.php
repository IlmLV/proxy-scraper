<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper;

use Cron\CronExpression;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Entities\ScrapedProxyList;
use IlmLV\ProxyScraper\Exceptions\ProxyScraperException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LoadProxies
{
    private ScrapedProxyList $proxies;

    protected HttpClientInterface $httpClient;

    /**
     * @var array<int, class-string<ProxyScraper>>
     */
    private array $scrapers = [
        Sources\AliilaproProxyListHttp::class,
        Sources\AliilaproProxyListSocks4::class,
        Sources\AliilaproProxyListSocks5::class,
        Sources\BlogspotProxyCom::class,
        Sources\CheckerProxyNet::class,
        Sources\ClarketmProxyList::class,
        Sources\FreeProxyListNet::class,
        Sources\FreeProxyListNetAnonymousProxy::class,
        Sources\FreeProxyListNetUkProxy::class,
        Sources\FreeProxyWorld::class,
        Sources\GeonodeProxyList::class,
        Sources\HookzofSocks5List::class,
        Sources\Mmpx12ProxyList::class,
        Sources\MonosansProxyListHttp::class,
        Sources\ProxiflyProxyList::class,
        Sources\Proxy11::class,
        Sources\ProxyListPlusHttp::class,
        Sources\ProxyScrapeComHttp::class,
        Sources\ProxyScrapeComSocks4::class,
        Sources\ProxyScrapeComSocks5::class,
        Sources\PubProxyCom::class,
        Sources\RoosterkidOpenProxyListHttps::class,
        Sources\RoosterkidOpenProxyListSocks4::class,
        Sources\RoosterkidOpenProxyListSocks5::class,
        Sources\ShiftyTRProxyListHttp::class,
        Sources\ShiftyTRProxyListHttps::class,
        Sources\ShiftyTRProxyListSocks4::class,
        Sources\ShiftyTRProxyListSocks5::class,
        Sources\SocksProxyNet::class,
        Sources\SpysMeProxyList::class,
        Sources\SslProxiesOrg::class,
        Sources\TheSpeedXProxyListHttp::class,
        Sources\TheSpeedXProxyListSocks4::class,
        Sources\TheSpeedXProxyListSocks5::class,
        Sources\UsProxyOrg::class,
        Sources\VakhovFreshProxyListHttp::class,
        Sources\VakhovFreshProxyListHttps::class,
        Sources\VakhovFreshProxyListSocks4::class,
        Sources\VakhovFreshProxyListSocks5::class,
    ];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $scraperConfig = [];

    /**
     * @var array<string, ProxyScraperException>
     */
    private array $errors = [];

    /**
     * @param array<string, array<string, mixed>> $scraperConfig
     */
    public function __construct(array $scraperConfig = [], ?HttpClientInterface $httpClient = null)
    {
        $this->scraperConfig += $scraperConfig;

        $this->httpClient = $httpClient ?? HttpClient::create([
            'timeout' => 30,
        ]);

        $this->proxies = new ScrapedProxyList();
    }

    /**
     * @param array<string, array<string, mixed>> $scraperConfig
     */
    public static function make(array $scraperConfig = [], ?HttpClientInterface $httpClient = null): LoadProxies
    {
        return new self($scraperConfig, $httpClient);
    }

    public function proxies(): ScrapedProxyList
    {
        return $this->proxies;
    }

    /**
     * @return Proxy[]
     */
    public function get(): array
    {
        return $this->proxies->get();
    }

    /**
     * Scraped proxies flattened across every source with duplicates removed.
     * See {@see ScrapedProxyList::unique()}.
     *
     * @return Proxy[]
     */
    public function unique(): array
    {
        return $this->proxies->unique();
    }

    /**
     * @return array<string, array<string, int>>
     */
    public function stats(): array
    {
        return $this->proxies->stats();
    }

    /**
     * @return array<string, ProxyScraperException>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Run every registered scraper now and return $this for result access
     * (get()/stats()/errors()). Running is idempotent — each scraper's result
     * replaces any previous one, so calling this more than once does not
     * accumulate duplicates.
     */
    public function all(): self
    {
        foreach ($this->scrapers as $scraper) {
            $this->run($scraper);
        }
        return $this;
    }

    /**
     * Run only the registered scrapers whose cron SCHEDULE is due now, then
     * return $this for result access.
     */
    public function scheduled(): self
    {
        foreach ($this->scrapers as $scraper) {
            if (self::schedulerIsDue($scraper::SCHEDULE)) {
                $this->run($scraper);
            }
        }
        return $this;
    }

    /**
     * Builder: register one or more extra scrapers (deduplicated) without running
     * anything. Follow with all()/scheduled() to execute.
     *
     * @param array<int, class-string<ProxyScraper>>|class-string<ProxyScraper> $scrapers
     */
    public function add(array|string $scrapers): self
    {
        $this->scrapers = array_values(array_unique(array_merge($this->scrapers, is_string($scrapers) ? [$scrapers] : $scrapers)));
        return $this;
    }

    /**
     * Restrict the registered set to exactly the given scraper(s) and run them
     * immediately, returning $this for result access.
     *
     * @param array<int, class-string<ProxyScraper>>|class-string<ProxyScraper> $scrapers
     */
    public function only(array|string $scrapers): self
    {
        $this->scrapers = is_string($scrapers) ? [$scrapers] : $scrapers;
        return $this->all();
    }

    /**
     * Run a single registered scraper, storing its proxies or capturing the
     * exception it raised. Internal step driven by all()/scheduled()/only().
     *
     * @param class-string<ProxyScraper> $scraper
     */
    private function run(string $scraper): void
    {
        $config = $this->scraperConfig[$scraper] ?? [];

        try {
            $result = (new $scraper($this->httpClient, $config))->get();

            $this->proxies->push($scraper, iterator_to_array($result, false));
        } catch (ProxyScraperException $e) {
            $this->errors[$scraper] = $e;
        } catch (\Throwable $e) {
            // Honor the "a failing source never aborts the batch" guarantee even
            // when a source throws something other than a ProxyScraperException
            // (e.g. a custom scraper raising a \TypeError/\RuntimeException): wrap
            // it so errors() stays uniformly typed and the original is preserved.
            $this->errors[$scraper] = new ScraperException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public static function schedulerIsDue(string $schedule): bool
    {
        return (new CronExpression($schedule))->isDue();
    }

}

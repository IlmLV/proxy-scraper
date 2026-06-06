<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper;

use Cron\CronExpression;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Entities\ScrapedProxyList;
use IlmLV\ProxyScraper\Exceptions\ProxyScraperException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LoadProxies
{
    /**
     * @var ScrapedProxyList
     */
    private ScrapedProxyList $proxies;

    /**
     * @var HttpClientInterface
     */
    protected HttpClientInterface $httpClient;

    /**
     * @var array<int, class-string<ProxyScraper&ScraperInterface>>
     */
    private array $scrapers = [
        \IlmLV\ProxyScraper\Sources\BlogspotProxyCom::class,
        \IlmLV\ProxyScraper\Sources\CheckerProxyNet::class,
        \IlmLV\ProxyScraper\Sources\ClarketmProxyList::class,
        \IlmLV\ProxyScraper\Sources\FreeProxyListNet::class,
        \IlmLV\ProxyScraper\Sources\FreeProxyListNetAnonymousProxy::class,
        \IlmLV\ProxyScraper\Sources\FreeProxyListNetUkProxy::class,
        \IlmLV\ProxyScraper\Sources\MonosansProxyListHttp::class,
        \IlmLV\ProxyScraper\Sources\ProxyScrapeComHttp::class,
        \IlmLV\ProxyScraper\Sources\ProxyScrapeComSocks4::class,
        \IlmLV\ProxyScraper\Sources\ProxyScrapeComSocks5::class,
        \IlmLV\ProxyScraper\Sources\PubProxyCom::class,
        \IlmLV\ProxyScraper\Sources\RoosterkidOpenProxyListHttps::class,
        \IlmLV\ProxyScraper\Sources\RoosterkidOpenProxyListSocks4::class,
        \IlmLV\ProxyScraper\Sources\RoosterkidOpenProxyListSocks5::class,
        \IlmLV\ProxyScraper\Sources\ShiftyTRProxyListHttp::class,
        \IlmLV\ProxyScraper\Sources\ShiftyTRProxyListHttps::class,
        \IlmLV\ProxyScraper\Sources\ShiftyTRProxyListSocks4::class,
        \IlmLV\ProxyScraper\Sources\ShiftyTRProxyListSocks5::class,
        \IlmLV\ProxyScraper\Sources\SocksProxyNet::class,
        \IlmLV\ProxyScraper\Sources\SslProxiesOrg::class,
        \IlmLV\ProxyScraper\Sources\TheSpeedXProxyListHttp::class,
        \IlmLV\ProxyScraper\Sources\TheSpeedXProxyListSocks4::class,
        \IlmLV\ProxyScraper\Sources\TheSpeedXProxyListSocks5::class,
        \IlmLV\ProxyScraper\Sources\UsProxyOrg::class,
        \IlmLV\ProxyScraper\Sources\VakhovFreshProxyListHttp::class,
        \IlmLV\ProxyScraper\Sources\VakhovFreshProxyListHttps::class,
        \IlmLV\ProxyScraper\Sources\VakhovFreshProxyListSocks4::class,
        \IlmLV\ProxyScraper\Sources\VakhovFreshProxyListSocks5::class,
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

        $this->proxies = new ScrapedProxyList;
    }

    /**
     * @param array<string, array<string, mixed>> $scraperConfig
     */
    public static function init(array $scraperConfig = [], ?HttpClientInterface $httpClient = null): LoadProxies
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

    public function all(): self
    {
        foreach ($this->scrapers as $scraper) {
            $this->run($scraper);
        }
        return $this;
    }

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
     * @param array<int, class-string<ProxyScraper&ScraperInterface>>|class-string<ProxyScraper&ScraperInterface> $scrapers
     */
public function add(array|string $scrapers): self
{
    $this->scrapers = array_values(array_unique(array_merge($this->scrapers, is_string($scrapers) ? [$scrapers] : $scrapers)));
    return $this;
}
    }

    /**
     * @param array<int, class-string<ProxyScraper&ScraperInterface>>|class-string<ProxyScraper&ScraperInterface> $scrapers
     */
    public function only(array|string $scrapers): self
    {
        $this->scrapers = is_string($scrapers) ? [$scrapers] : $scrapers;
        return $this->all();
    }

    /**
     * @param class-string<ProxyScraper&ScraperInterface> $scraper
     */
    public function run(string $scraper): void
    {
        $config = $this->scraperConfig[$scraper] ?? [];

        try {
            $result = (new $scraper($this->httpClient, $config))->get();

            foreach($result as $item) {
                $this->proxies->push($scraper, [$item]);
            }
        }
        catch (ProxyScraperException $e) {
            $this->errors[$scraper] = $e;
        }
    }

    public static function schedulerIsDue(string $schedule): bool
    {
        return (new CronExpression($schedule))->isDue();
    }

}

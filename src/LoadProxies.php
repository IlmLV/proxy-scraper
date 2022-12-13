<?php

namespace IlmLV\ProxyScraper;

use Cron\CronExpression;
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

    private array $scrapers = [
        \IlmLV\ProxyScraper\Sources\BlogspotProxyCom::class,
        \IlmLV\ProxyScraper\Sources\CheckerProxyNet::class,
        \IlmLV\ProxyScraper\Sources\ClarketmProxyList::class,
        \IlmLV\ProxyScraper\Sources\FreeProxyListNet::class,
        \IlmLV\ProxyScraper\Sources\FreeProxyListNetAnonymousProxy::class,
        \IlmLV\ProxyScraper\Sources\FreeProxyListNetUkProxy::class,
        \IlmLV\ProxyScraper\Sources\GimmeProxyCom::class,
        \IlmLV\ProxyScraper\Sources\MultiproxyOrg::class,
        \IlmLV\ProxyScraper\Sources\ProxyServerList24Top::class,
        \IlmLV\ProxyScraper\Sources\PubProxyCom::class,
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
    ];

    private array $scraperConfig = [];

    private array $errors = [];

    public function __construct(array $scraperConfig = [], HttpClientInterface $httpClient = null)
    {
        $this->scraperConfig += $scraperConfig;

        $this->httpClient = $httpClient ?? HttpClient::create([
            'timeout' => 30,
        ]);

        $this->proxies = new ScrapedProxyList;
    }

    public static function init(array $scraperConfig = [], HttpClientInterface $httpClient = null): LoadProxies
    {
        return new self($scraperConfig, $httpClient);
    }

    public function proxies(): ScrapedProxyList
    {
        return $this->proxies;
    }

    public function get(): array
    {
        return $this->proxies->get();
    }

    public function stats(): array
    {
        return $this->proxies->stats();
    }

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

    public function add(array|string $scrapers): self
    {
        $this->scrapers += is_string($scrapers) ? [$scrapers] : $scrapers;
        return $this;
    }

    public function only(array|string $scrapers): self
    {
        $this->scrapers = is_string($scrapers) ? [$scrapers] : $scrapers;
        return $this->all();
    }

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

<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper;

use Generator;
use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Entities\Port;
use IlmLV\ProxyScraper\Entities\Protocol;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Base for every scraper. Concrete scrapers (Scrapers\*) implement get(); the
 * bundled sources (Sources\*) extend one of those and configure it by overriding
 * protected properties.
 *
 * Contract for subclasses:
 * - string $url           required — the source endpoint; may contain sprintf
 *                          placeholders filled via getUrl(...).
 * - const SCHEDULE        cron expression controlling LoadProxies::scheduled().
 * - fetch()               GET $url (with options applied) as a string, or throw.
 * - makeProxy()           build a validated Proxy from raw ip/port/protocol.
 *
 * Per-format scrapers add their own config properties (e.g. $protocol, $rowPath,
 * column indices, JSON field names) — see each Scrapers\* class.
 */
abstract class ProxyScraper implements ScraperInterface
{
    protected string $url;

    /**
     * @var array<string, mixed>
     */
    protected array $options;

    /** @var string */
    public const SCHEDULE = '* * * * *';

    protected HttpClientInterface $httpClient;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(HttpClientInterface $httpClient, array $options = [])
    {
        $this->httpClient = $httpClient;

        $this->loadOptions($options);
    }

    /**
     * Yield the proxies this source publishes. Concrete scrapers (the Scrapers\*
     * format bases, or a source extending ProxyScraper directly) implement this.
     *
     * @return Generator<int, Proxy>
     */
    abstract public function get(): Generator;

    /**
     * @param array<string, mixed> $options
     */
    private function loadOptions(array $options): void
    {
        foreach ($options as $key => $value) {
            $methodName = Str::snakeToCamel('set_'. $key);
            if (method_exists($this, $methodName)) {
                $this->{$methodName}($value);

                unset($options[$key]);
            }
        }

        $this->options = $this->processOptions($options);
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function processOptions(array $options): array
    {
        // cast all booleans as string
        return array_map(function ($a) {
            if (is_bool($a)) {
                return $a === true ? 'true' : 'false';
            }
            return $a;
        }, $options);
    }

    protected function getUrl(string ...$values): string
    {
        $url = $values === [] ? $this->url : sprintf($this->url, ...$values);

        if ($this->options) {
            // Several sources already carry a query string (e.g. pubproxy.com's
            // "?limit=5&format=json"); append with "&" in that case so configured
            // options don't produce a second, URL-breaking "?".
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($this->options);
        }

        return $url;
    }

    /**
     * GET the built source URL (see {@see getUrl()}) and return the response
     * body, translating any transport/HTTP failure into a ScraperException.
     *
     * @throws ScraperException
     */
    protected function fetch(): string
    {
        return $this->fetchUrl($this->getUrl());
    }

    /**
     * GET an explicit URL and return the response body, translating any
     * transport/HTTP failure into a ScraperException. Use this for sources that
     * fetch more than one endpoint (e.g. an index then a detail URL); the
     * no-argument {@see fetch()} covers the common single-URL case.
     *
     * @throws ScraperException
     */
    protected function fetchUrl(string $url): string
    {
        try {
            return $this->httpClient->request('GET', $url)->getContent();
        } catch (\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function makeProxy(string $host, int|string $port, string $protocol): Proxy
    {
        return new Proxy(Protocol::fromString($protocol), new Host($host), new Port($port));
    }
}

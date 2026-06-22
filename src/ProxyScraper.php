<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper;

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
abstract class ProxyScraper
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
     * @param array<string, mixed> $options
     */
    private function loadOptions(array &$options = []): void
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
            $url .= '?' . http_build_query($this->options);
        }

        return $url;
    }

    /**
     * GET the built source URL and return the response body, translating any
     * transport/HTTP failure into a ScraperException.
     *
     * @throws ScraperException
     */
    protected function fetch(): string
    {
        try {
            return $this->httpClient->request('GET', $this->getUrl())->getContent();
        } catch (\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function makeProxy(string $ip, int|string $port, string $protocol): Proxy
    {
        return new Proxy(new Protocol($protocol), new Host($ip), new Port($port));
    }
}

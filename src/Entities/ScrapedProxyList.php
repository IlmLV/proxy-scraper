<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Entities;

class ScrapedProxyList
{
    /**
     * @var array<string, Proxy[]>
     */
    private array $proxiesByScraper = [];

    /**
     * Store the proxies a scraper produced, replacing any previous result for
     * that scraper. Keying by source makes re-running a scraper idempotent
     * instead of appending duplicates.
     *
     * @param Proxy[] $proxies
     */
    public function push(string $scraper, array $proxies): void
    {
        $this->proxiesByScraper[$scraper] = $proxies;
    }

    /**
     * @return Proxy[]
     */
    public function get(): array
    {
        if ($this->proxiesByScraper === []) {
            return [];
        }

        return array_merge(...array_values($this->proxiesByScraper));
    }

    /**
     * Every scraped proxy flattened across sources, with duplicates removed.
     * Two proxies are equal when their string form
     * ("protocol://[user:pass@]host:port") matches, so the same endpoint reached
     * over a different protocol is kept. Sources overlap heavily, so this is the
     * method to use for a single working set; first occurrence wins and order is
     * preserved. get() keeps every occurrence (and is what stats() counts).
     *
     * @return Proxy[]
     */
    public function unique(): array
    {
        $unique = [];
        foreach ($this->get() as $proxy) {
            $unique[(string) $proxy] ??= $proxy;
        }

        return array_values($unique);
    }

    /**
     * @return array<string, array<string, int>>
     */
    public function stats(): array
    {
        return array_map(function ($proxies) {
            $stats = [];
            foreach ($proxies as $proxy) {
                $stats[$proxy->protocol->value] = ($stats[$proxy->protocol->value] ?? 0) + 1;
            }
            return $stats;
        }, $this->proxiesByScraper);
    }
}

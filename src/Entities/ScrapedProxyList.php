<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Entities;

class ScrapedProxyList
{
    /**
     * @var array<string, Proxy[]>
     */
    private array $array = [];

    /**
     * @param string $scraper
     * @param Proxy[] $proxies
     * @return void
     */
    public function push(string $scraper, array $proxies): void
    {
        $this->array[$scraper] = array_merge(($this->array[$scraper] ?? []), $proxies);
    }

    /**
     * @return Proxy[]
     */
    public function get(): array
    {
        return array_merge(...array_values($this->array));
    }

    /**
     * @return array<string, array<string, int>>
     */
    public function stats(): array
    {
        return array_map(function ($proxies){
            $stats = [];
            foreach ($proxies as $proxy) {
                $stats[(string)$proxy->protocol] = ($stats[(string)$proxy->protocol] ?? 0) + 1;
            }
            return $stats;
        }, $this->array);
    }
}
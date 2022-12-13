<?php

namespace IlmLV\ProxyScraper\Entities;

class ScrapedProxyList
{
    /**
     * @var array
     */
    private array $array = [];

    /**
     * @param string $scraper
     * @param array $proxies
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
        $result = [];
        foreach ($this->array as $proxies) {
            $result += $proxies;
        }
        return $result;
    }

    /**
     * @return array
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
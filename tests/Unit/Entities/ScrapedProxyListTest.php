<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Entities;

use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Entities\ScrapedProxyList;
use PHPUnit\Framework\TestCase;

class ScrapedProxyListTest extends TestCase
{
    public function testPushReplacesPreviousResultForSameScraper(): void
    {
        $list = new ScrapedProxyList();
        $list->push('ScraperA', [new Proxy('http://1.2.3.4:8080')]);
        // Re-pushing the same scraper replaces its result rather than appending,
        // so re-running a source does not accumulate duplicates.
        $list->push('ScraperA', [new Proxy('http://5.6.7.8:3128')]);

        $result = $list->get();

        $this->assertCount(1, $result);
        $this->assertSame('5.6.7.8', (string) $result[0]->host);
    }

    public function testGetMergesProxiesFromMultipleScrapers(): void
    {
        $list = new ScrapedProxyList();
        // Two scrapers whose list indices overlap (0,1,2 vs 0,1): a naive
        // array-union (+=) would keep only the first scraper's collisions and
        // silently drop the rest. get() must return every proxy.
        $list->push('ScraperA', [
            new Proxy('http://1.1.1.1:80'),
            new Proxy('http://2.2.2.2:80'),
            new Proxy('http://3.3.3.3:80'),
        ]);
        $list->push('ScraperB', [
            new Proxy('http://4.4.4.4:80'),
            new Proxy('http://5.5.5.5:80'),
        ]);

        $result = $list->get();

        $this->assertCount(5, $result);
        $this->assertSame(
            ['1.1.1.1', '2.2.2.2', '3.3.3.3', '4.4.4.4', '5.5.5.5'],
            array_map(static fn (Proxy $p): string => (string) $p->host, $result)
        );
    }

    public function testStatsCountByScraperAndProtocol(): void
    {
        $list = new ScrapedProxyList();
        $list->push('ScraperA', [
            new Proxy('http://1.2.3.4:8080'),
            new Proxy('https://5.6.7.8:443'),
            new Proxy('http://9.10.11.12:80'),
        ]);
        $list->push('ScraperB', [new Proxy('socks5://1.1.1.1:1080')]);

        $this->assertSame(
            [
                'ScraperA' => ['http' => 2, 'https' => 1],
                'ScraperB' => ['socks5' => 1],
            ],
            $list->stats()
        );
    }

    public function testEmptyListIsEmpty(): void
    {
        $list = new ScrapedProxyList();

        $this->assertSame([], $list->get());
        $this->assertSame([], $list->stats());
    }
}

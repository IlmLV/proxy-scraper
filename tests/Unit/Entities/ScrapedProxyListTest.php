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
        $list->push('ScraperA', [Proxy::fromString('http://1.2.3.4:8080')]);
        // Re-pushing the same scraper replaces its result rather than appending,
        // so re-running a source does not accumulate duplicates.
        $list->push('ScraperA', [Proxy::fromString('http://5.6.7.8:3128')]);

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
            Proxy::fromString('http://1.1.1.1:80'),
            Proxy::fromString('http://2.2.2.2:80'),
            Proxy::fromString('http://3.3.3.3:80'),
        ]);
        $list->push('ScraperB', [
            Proxy::fromString('http://4.4.4.4:80'),
            Proxy::fromString('http://5.5.5.5:80'),
        ]);

        $result = $list->get();

        $this->assertCount(5, $result);
        $this->assertSame(
            ['1.1.1.1', '2.2.2.2', '3.3.3.3', '4.4.4.4', '5.5.5.5'],
            array_map(static fn (Proxy $p): string => (string) $p->host, $result)
        );
    }

    public function testUniqueRemovesCrossSourceDuplicatesPreservingOrder(): void
    {
        $list = new ScrapedProxyList();
        $list->push('ScraperA', [
            Proxy::fromString('http://1.1.1.1:80'),
            Proxy::fromString('http://2.2.2.2:80'),
        ]);
        $list->push('ScraperB', [
            Proxy::fromString('http://1.1.1.1:80'),    // duplicate of ScraperA's first
            Proxy::fromString('socks5://1.1.1.1:80'),  // same host:port, different protocol — kept
            Proxy::fromString('http://3.3.3.3:80'),
        ]);

        // get() keeps every occurrence; unique() collapses the exact duplicate only.
        $this->assertCount(5, $list->get());
        $this->assertSame(
            ['http://1.1.1.1:80', 'http://2.2.2.2:80', 'socks5://1.1.1.1:80', 'http://3.3.3.3:80'],
            array_map(static fn (Proxy $p): string => (string) $p, $list->unique())
        );
    }

    public function testUniqueOnEmptyListIsEmpty(): void
    {
        $this->assertSame([], (new ScrapedProxyList())->unique());
    }

    public function testStatsCountByScraperAndProtocol(): void
    {
        $list = new ScrapedProxyList();
        $list->push('ScraperA', [
            Proxy::fromString('http://1.2.3.4:8080'),
            Proxy::fromString('https://5.6.7.8:443'),
            Proxy::fromString('http://9.10.11.12:80'),
        ]);
        $list->push('ScraperB', [Proxy::fromString('socks5://1.1.1.1:1080')]);

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

<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Entities;

use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Entities\ScrapedProxyList;
use PHPUnit\Framework\TestCase;

class ScrapedProxyListTest extends TestCase
{
    public function testPushAccumulatesPerScraper(): void
    {
        $list = new ScrapedProxyList();
        $list->push('ScraperA', [new Proxy('http://1.2.3.4:8080')]);
        $list->push('ScraperA', [new Proxy('http://5.6.7.8:3128')]);

        $this->assertCount(2, $list->get());
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

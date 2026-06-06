<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Scrapers;

use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Tests\Support\StubJsonListScrapper;
use PHPUnit\Framework\TestCase;

class JsonListScrapperTest extends TestCase
{
    public function testParsesRootArrayList(): void
    {
        $scraper = new StubJsonListScrapper(MockClientFactory::fromFixture('Sources/jsonlist-root.json'));

        $proxies = iterator_to_array($scraper->get(), false);

        $this->assertCount(2, $proxies);
        $this->assertSame('http://1.2.3.4:8080', (string) $proxies[0]);
        $this->assertSame('socks5://5.6.7.8:3128', (string) $proxies[1]);
    }
}

<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Scrapers;

use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\Sources\ClarketmProxyList;
use IlmLV\ProxyScraper\Sources\ShiftyTRProxyListSocks5;
use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use PHPUnit\Framework\TestCase;

class TextListScraperTest extends TestCase
{
    public function testParsesIpPortLinesAndSkipsInvalid(): void
    {
        $scraper = new ClarketmProxyList(MockClientFactory::fromFixture('Sources/text-list.txt'));

        $proxies = iterator_to_array($scraper->get(), false);

        // 3 valid lines; the "not-a-proxy-line" and trailing blank line are skipped
        $this->assertCount(3, $proxies);
        $this->assertSame('http://1.2.3.4:8080', (string) $proxies[0]);
        $this->assertSame('http://9.10.11.12:80', (string) $proxies[2]);
    }

    public function testAppliesConfiguredProtocol(): void
    {
        $scraper = new ShiftyTRProxyListSocks5(MockClientFactory::fromFixture('Sources/text-list.txt'));

        $proxies = iterator_to_array($scraper->get(), false);

        $this->assertSame('socks5', (string) $proxies[0]->protocol);
    }

    public function testHttpFailureThrowsScraperException(): void
    {
        $scraper = new ClarketmProxyList(MockClientFactory::fromString('', 500));

        $this->expectException(ScraperException::class);
        iterator_to_array($scraper->get());
    }
}

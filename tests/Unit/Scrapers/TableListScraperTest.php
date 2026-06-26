<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Scrapers;

use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\Sources\FreeProxyListNet;
use IlmLV\ProxyScraper\Sources\SocksProxyNet;
use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use PHPUnit\Framework\TestCase;

class TableListScraperTest extends TestCase
{
    public function testParsesTableRowsAndSkipsInvalidRow(): void
    {
        $scraper = new FreeProxyListNet(MockClientFactory::fromFixture('Sources/table-http.html'));

        $proxies = iterator_to_array($scraper->get(), false);

        // 2 valid rows; the row with port 0 is skipped
        $this->assertCount(2, $proxies);
        $this->assertSame('http://1.2.3.4:8080', (string) $proxies[0]);
        $this->assertSame('http://5.6.7.8:3128', (string) $proxies[1]);
    }

    public function testReadsProtocolFromColumnWhenNotOverridden(): void
    {
        $scraper = new SocksProxyNet(MockClientFactory::fromFixture('Sources/table-socks.html'));

        $proxies = iterator_to_array($scraper->get(), false);

        $this->assertCount(2, $proxies);
        $this->assertSame('socks4', $proxies[0]->protocol->value);
        $this->assertSame('socks5', $proxies[1]->protocol->value);
    }

    public function testSkipsRowsWithMissingCells(): void
    {
        // A short/empty row (header, spacer, malformed) must be skipped without
        // letting Crawler::text() throw and abort the whole scrape.
        $html = '<table id="list"><tbody>'
            . '<tr><td>1.2.3.4</td><td>8080</td><td>US</td></tr>'
            . '<tr></tr>'
            . '<tr><td>5.6.7.8</td></tr>'
            . '<tr><td>9.10.11.12</td><td>3128</td><td>DE</td></tr>'
            . '</tbody></table>';
        $scraper = new FreeProxyListNet(MockClientFactory::fromString($html));

        $proxies = array_map('strval', iterator_to_array($scraper->get(), false));

        $this->assertSame(['http://1.2.3.4:8080', 'http://9.10.11.12:3128'], $proxies);
    }

    public function testHttpFailureThrowsScraperException(): void
    {
        $scraper = new FreeProxyListNet(MockClientFactory::fromString('', 503));

        $this->expectException(ScraperException::class);
        iterator_to_array($scraper->get());
    }
}

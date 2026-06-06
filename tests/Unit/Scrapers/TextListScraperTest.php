<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Scrapers;

use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\Sources\ClarketmProxyList;
use IlmLV\ProxyScraper\Sources\ProxiflyProxyList;
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

    public function testNullProtocolReadsSchemeFromEachLine(): void
    {
        // ProxiflyProxyList leaves $protocol null, so the scheme is read per line
        $body = "http://1.2.3.4:8080\nsocks5://5.6.7.8:1080\nsocks4://9.10.11.12:1081\n";
        $scraper = new ProxiflyProxyList(MockClientFactory::fromString($body));

        $proxies = array_map('strval', iterator_to_array($scraper->get(), false));

        $this->assertSame(
            ['http://1.2.3.4:8080', 'socks5://5.6.7.8:1080', 'socks4://9.10.11.12:1081'],
            $proxies
        );
    }

    public function testNullProtocolSkipsSchemelessAndInvalidLines(): void
    {
        // With $protocol null a bare "ip:port" line has no scheme to parse and is dropped
        $body = "http://1.2.3.4:8080\n5.6.7.8:3128\nnot-a-proxy-line\n";
        $scraper = new ProxiflyProxyList(MockClientFactory::fromString($body));

        $proxies = array_map('strval', iterator_to_array($scraper->get(), false));

        $this->assertSame(['http://1.2.3.4:8080'], $proxies);
    }

    public function testTrimsCrlfAndSurroundingWhitespace(): void
    {
        // A source served with CRLF endings (or padded lines) must not leave a
        // trailing \r on the port, which would fail Port validation and silently
        // drop every proxy.
        $body = "1.2.3.4:8080\r\n  5.6.7.8:3128  \r\n\r\n9.10.11.12:80\r\n";
        $scraper = new ClarketmProxyList(MockClientFactory::fromString($body));

        $proxies = iterator_to_array($scraper->get(), false);

        $this->assertSame(
            ['http://1.2.3.4:8080', 'http://5.6.7.8:3128', 'http://9.10.11.12:80'],
            array_map(static fn ($p): string => (string) $p, $proxies)
        );
    }

    public function testHttpFailureThrowsScraperException(): void
    {
        $scraper = new ClarketmProxyList(MockClientFactory::fromString('', 500));

        $this->expectException(ScraperException::class);
        iterator_to_array($scraper->get());
    }
}

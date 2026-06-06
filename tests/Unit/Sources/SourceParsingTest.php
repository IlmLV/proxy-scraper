<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Sources;
use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Tests\Support\Registry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Provider-level parsing tests: each Source is fed a recorded fixture through
 * MockHttpClient and asserted to extract the expected proxies. Grouped by the
 * scraper base type each Source uses.
 */
class SourceParsingTest extends TestCase
{
    #[DataProvider('textListSourceProvider')]
    public function testTextListSources(string $class, string $protocol): void
    {
        $scraper = new $class(MockClientFactory::fromFixture('Sources/text-list.txt'));

        $proxies = iterator_to_array($scraper->get(), false);

        $this->assertCount(3, $proxies);
        $this->assertSame($protocol . '://1.2.3.4:8080', (string) $proxies[0]);
        foreach ($proxies as $proxy) {
            $this->assertSame($protocol, (string) $proxy->protocol);
        }
    }

    public static function textListSourceProvider(): array
    {
        return [
            'clarketm'         => [Sources\ClarketmProxyList::class, 'http'],
            'shifty http'      => [Sources\ShiftyTRProxyListHttp::class, 'http'],
            'shifty https'     => [Sources\ShiftyTRProxyListHttps::class, 'https'],
            'shifty socks4'    => [Sources\ShiftyTRProxyListSocks4::class, 'socks4'],
            'shifty socks5'    => [Sources\ShiftyTRProxyListSocks5::class, 'socks5'],
            'thespeedx http'   => [Sources\TheSpeedXProxyListHttp::class, 'http'],
            'thespeedx socks4' => [Sources\TheSpeedXProxyListSocks4::class, 'socks4'],
            'thespeedx socks5' => [Sources\TheSpeedXProxyListSocks5::class, 'socks5'],
        ];
    }

    #[DataProvider('tableHttpSourceProvider')]
    public function testHtmlTableHttpSources(string $class): void
    {
        $scraper = new $class(MockClientFactory::fromFixture('Sources/table-http.html'));

        $proxies = iterator_to_array($scraper->get(), false);

        $this->assertCount(2, $proxies);
        $this->assertSame('http://1.2.3.4:8080', (string) $proxies[0]);
        $this->assertSame('http://5.6.7.8:3128', (string) $proxies[1]);
    }

    public static function tableHttpSourceProvider(): array
    {
        return [
            'free-proxy-list'        => [Sources\FreeProxyListNet::class],
            'free-proxy-list anon'   => [Sources\FreeProxyListNetAnonymousProxy::class],
            'free-proxy-list uk'     => [Sources\FreeProxyListNetUkProxy::class],
            'sslproxies'             => [Sources\SslProxiesOrg::class],
            'us-proxy'               => [Sources\UsProxyOrg::class],
        ];
    }

    public function testSocksTableSource(): void
    {
        $scraper = new Sources\SocksProxyNet(MockClientFactory::fromFixture('Sources/table-socks.html'));

        $proxies = iterator_to_array($scraper->get(), false);

        $this->assertCount(2, $proxies);
        $this->assertSame('socks4://1.2.3.4:1080', (string) $proxies[0]);
        $this->assertSame('socks5://5.6.7.8:1081', (string) $proxies[1]);
    }

    public function testPubProxyComJsonList(): void
    {
        $scraper = new Sources\PubProxyCom(MockClientFactory::fromFixture('Sources/pubproxy.json'));

        $proxies = iterator_to_array($scraper->get(), false);

        $this->assertCount(2, $proxies);
        $this->assertSame('http://1.2.3.4:8080', (string) $proxies[0]);
        $this->assertSame('https://5.6.7.8:3128', (string) $proxies[1]);
    }

    public function testBlogspotXmlFeed(): void
    {
        $scraper = new Sources\BlogspotProxyCom(MockClientFactory::fromFixture('Sources/blogspot.xml'));

        $proxies = array_map('strval', iterator_to_array($scraper->get(), false));

        // ip:port (colon) and ip\tport (tab) forms are both extracted, http is prepended
        $this->assertContains('http://1.2.3.4:8080', $proxies);
        $this->assertContains('http://5.6.7.8:3128', $proxies);
        $this->assertContains('http://9.10.11.12:8888', $proxies);
        $this->assertContains('http://200.1.2.3:80', $proxies);
    }

    public function testCheckerProxyNetResolvesLatestArchiveThenFetchesProxies(): void
    {
        // two-request flow: archive index, then the latest date's proxy list
        $client = MockClientFactory::sequence([
            'Sources/checkerproxy-index.json',
            'Sources/checkerproxy-date.json',
        ]);
        $scraper = new Sources\CheckerProxyNet($client);

        $proxies = iterator_to_array($scraper->get(), false);

        $this->assertCount(3, $proxies);
        $this->assertSame('http://1.2.3.4:1080', (string) $proxies[0]);
        $this->assertSame('http://9.10.11.12:3128', (string) $proxies[2]);
    }

    public function testEveryRegisteredSourceImplementsScraperInterface(): void
    {
        foreach (Registry::scrapers() as $class) {
            $this->assertTrue(
                is_subclass_of($class, ScraperInterface::class),
                $class . ' must implement ScraperInterface'
            );
        }
    }
}

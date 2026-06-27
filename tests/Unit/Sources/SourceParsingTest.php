<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Sources;
use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Tests\Support\Registry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;

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
            $this->assertSame($protocol, $proxy->protocol->value);
        }
    }

    public static function textListSourceProvider(): array
    {
        return [
            'clarketm'       => [Sources\ClarketmProxyList::class, 'http'],
            'hookzof socks5' => [Sources\HookzofSocks5List::class, 'socks5'],
            'monosans http'  => [Sources\MonosansProxyListHttp::class, 'http'],
        ];
    }

    /**
     * @param string[] $protocols
     */
    #[DataProvider('multiProtocolSourceProvider')]
    public function testMultiProtocolSources(string $class, array $protocols): void
    {
        // One list per protocol; the same fixture body is served for every URL
        // (a fresh MockResponse per request, since each is consumed once).
        $body = MockClientFactory::load('Sources/text-list.txt');
        $client = MockClientFactory::router(fn (): MockResponse => new MockResponse($body));

        $scraper = new $class($client);
        $proxies = iterator_to_array($scraper->get(), false);

        // text-list.txt has 3 valid lines, fetched once per protocol.
        $this->assertCount(3 * count($protocols), $proxies);
        $seen = array_values(array_unique(array_map(static fn ($p): string => $p->protocol->value, $proxies)));
        $this->assertSame($protocols, $seen);
    }

    public static function multiProtocolSourceProvider(): array
    {
        return [
            'aliilapro'   => [Sources\Aliilapro::class, ['http', 'socks4', 'socks5']],
            'proxyscrape' => [Sources\ProxyScrapeCom::class, ['http', 'socks4', 'socks5']],
            'roosterkid'  => [Sources\Roosterkid::class, ['https', 'socks4', 'socks5']],
            'shiftytr'    => [Sources\ShiftyTR::class, ['http', 'https', 'socks4', 'socks5']],
            'thespeedx'   => [Sources\TheSpeedX::class, ['http', 'socks4', 'socks5']],
            'vakhov'      => [Sources\Vakhov::class, ['http', 'https', 'socks4', 'socks5']],
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

    public function testPrefixedListSourcePreservesPerLineProtocol(): void
    {
        // Lines already carry the scheme; the protocol is read per line, not prepended.
        $scraper = new Sources\ProxiflyProxyList(MockClientFactory::fromFixture('Sources/prefixed-list.txt'));

        $proxies = array_map('strval', iterator_to_array($scraper->get(), false));

        // 3 scheme-prefixed lines parse; the garbage line and the scheme-less line are skipped
        $this->assertSame(
            ['http://1.2.3.4:8080', 'socks5://5.6.7.8:1080', 'socks4://9.10.11.12:1081'],
            $proxies
        );
    }

    public function testGeonodeJsonListYieldsOneProxyPerProtocol(): void
    {
        $scraper = new Sources\GeonodeProxyList(MockClientFactory::fromFixture('Sources/geonode.json'));

        $proxies = array_map('strval', iterator_to_array($scraper->get(), false));

        // the third entry lists two protocols, so it expands into two proxies
        $this->assertSame(
            ['http://1.2.3.4:8080', 'socks5://5.6.7.8:1080', 'http://9.10.11.12:3128', 'https://9.10.11.12:3128'],
            $proxies
        );
    }

    public function testSpysMeWhitespaceListSkipsBanner(): void
    {
        $scraper = new Sources\SpysMeProxyList(MockClientFactory::fromFixture('Sources/spys-me.txt'));

        $proxies = array_map('strval', iterator_to_array($scraper->get(), false));

        // banner/legend lines and the malformed trailing line are skipped; ip:port is the first token
        $this->assertSame(['http://1.2.3.4:8080', 'http://5.6.7.8:3128'], $proxies);
    }

    public function testProxyListPlusHttpTable(): void
    {
        $scraper = new Sources\ProxyListPlusHttp(MockClientFactory::fromFixture('Sources/proxylistplus.html'));

        $proxies = array_map('strval', iterator_to_array($scraper->get(), false));

        // IP is the 2nd cell (1st is a flag), port the 3rd; the trailing empty row is skipped
        $this->assertSame(['http://1.2.3.4:8080', 'http://5.6.7.8:3128'], $proxies);
    }

    public function testFreeProxyWorldReadsProtocolPerRow(): void
    {
        $scraper = new Sources\FreeProxyWorld(MockClientFactory::fromFixture('Sources/freeproxy-world.html'));

        $proxies = array_map('strval', iterator_to_array($scraper->get(), false));

        // protocol comes from the "Type" column (index 5), so the two rows differ
        $this->assertSame(['http://1.2.3.4:8080', 'socks5://5.6.7.8:1080'], $proxies);
    }

    public function testProxy11Table(): void
    {
        $scraper = new Sources\Proxy11(MockClientFactory::fromFixture('Sources/proxy11.html'));

        $proxies = array_map('strval', iterator_to_array($scraper->get(), false));

        // IP is wrapped in <code>; text() still extracts it
        $this->assertSame(['http://1.2.3.4:8080', 'http://5.6.7.8:3128'], $proxies);
    }

    public function testMmpx12SkipsCorruptPrefixedLine(): void
    {
        $scraper = new Sources\Mmpx12ProxyList(MockClientFactory::fromFixture('Sources/mmpx12.txt'));

        $proxies = array_map('strval', iterator_to_array($scraper->get(), false));

        // the "error code: 502" line has an extra colon and fails to parse, so it is dropped
        $this->assertSame(
            ['http://1.2.3.4:8080', 'socks4://5.6.7.8:1080', 'socks5://9.10.11.12:1081'],
            $proxies
        );
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

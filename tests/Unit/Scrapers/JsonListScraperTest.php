<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Scrapers;

use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\Scrapers\JsonListScraper;
use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Tests\Support\StubJsonListScraper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;

class JsonListScraperTest extends TestCase
{
    public function testParsesRootArrayList(): void
    {
        $scraper = new StubJsonListScraper(MockClientFactory::fromFixture('Sources/jsonlist-root.json'));

        $proxies = iterator_to_array($scraper->get(), false);

        $this->assertCount(2, $proxies);
        $this->assertSame('http://1.2.3.4:8080', (string) $proxies[0]);
        $this->assertSame('socks5://5.6.7.8:3128', (string) $proxies[1]);
    }

    public function testSkipsMalformedItemsInsteadOfAborting(): void
    {
        $body = json_encode([
            ['ip' => '1.2.3.4', 'port' => '8080', 'protocol' => 'http'],
            ['ip' => '9.9.9.9'],                                       // missing port/protocol
            ['ip' => '8.8.8.8', 'port' => '80', 'protocol' => 'ftp'],  // unsupported protocol
            ['ip' => '5.6.7.8', 'port' => '3128', 'protocol' => 'socks5'],
        ]);

        $scraper = new StubJsonListScraper(MockClientFactory::fromString($body));

        $proxies = iterator_to_array($scraper->get(), false);

        // The two bad entries are skipped; the valid ones are still yielded.
        $this->assertCount(2, $proxies);
        $this->assertSame('http://1.2.3.4:8080', (string) $proxies[0]);
        $this->assertSame('socks5://5.6.7.8:3128', (string) $proxies[1]);
    }

    public function testThrowsWhenListContainerMissing(): void
    {
        $scraper = new StubJsonListScraper(MockClientFactory::fromString('not a json list'));

        $this->expectException(ScraperException::class);

        iterator_to_array($scraper->get(), false);
    }

    public function testProtocolsMapForcesProtocolOverridingField(): void
    {
        // Items carry no protocol field; the map key supplies it per fetched list
        // (and would override one if present). The same body is served for both URLs.
        $body = json_encode([['ip' => '1.2.3.4', 'port' => '8080']]);
        $client = MockClientFactory::router(fn (): MockResponse => new MockResponse($body));

        $scraper = new class ($client) extends JsonListScraper {
            protected array $protocols = [
                'http' => 'https://json.test/http',
                'socks5' => 'https://json.test/socks5',
            ];
        };

        $proxies = array_map('strval', iterator_to_array($scraper->get(), false));

        $this->assertSame(['http://1.2.3.4:8080', 'socks5://1.2.3.4:8080'], $proxies);
    }
}

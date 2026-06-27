<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Scrapers;

use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\Scrapers\JsonScraper;
use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Tests\Support\StubJsonScraper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;

class JsonScraperTest extends TestCase
{
    public function testParsesSingleJsonObject(): void
    {
        $scraper = new StubJsonScraper(MockClientFactory::fromFixture('Sources/jsonobject.json'));

        $proxies = iterator_to_array($scraper->get(), false);

        $this->assertCount(1, $proxies);
        $this->assertSame('http://1.2.3.4:8080', (string) $proxies[0]);
    }

    public function testMissingPropertyThrowsScraperException(): void
    {
        $scraper = new StubJsonScraper(MockClientFactory::fromString('{"ip":"1.2.3.4"}'));

        $this->expectException(ScraperException::class);
        iterator_to_array($scraper->get());
    }

    public function testHttpFailureThrowsScraperException(): void
    {
        $scraper = new StubJsonScraper(MockClientFactory::fromString('', 500));

        $this->expectException(ScraperException::class);
        iterator_to_array($scraper->get());
    }

    public function testProtocolsMapYieldsOneForcedProxyPerUrl(): void
    {
        // Single-object endpoint per protocol; the map key forces each proxy's
        // protocol (the object carries none). The same body is served for both URLs.
        $body = json_encode(['ip' => '1.2.3.4', 'port' => '8080']);
        $client = MockClientFactory::router(fn (): MockResponse => new MockResponse($body));

        $scraper = new class ($client) extends JsonScraper {
            protected array $protocols = [
                'http' => 'https://json.test/http',
                'socks5' => 'https://json.test/socks5',
            ];
        };

        $proxies = array_map('strval', iterator_to_array($scraper->get(), false));

        $this->assertSame(['http://1.2.3.4:8080', 'socks5://1.2.3.4:8080'], $proxies);
    }
}

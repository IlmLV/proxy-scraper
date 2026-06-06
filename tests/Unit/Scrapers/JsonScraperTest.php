<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Scrapers;

use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Tests\Support\StubJsonScraper;
use PHPUnit\Framework\TestCase;

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
}

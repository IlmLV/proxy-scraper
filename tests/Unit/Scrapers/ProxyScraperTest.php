<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Scrapers;

use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Tests\Support\StubScraper;
use Symfony\Component\HttpClient\MockHttpClient;
use PHPUnit\Framework\TestCase;

class ProxyScraperTest extends TestCase
{
    public function testGetUrlAppliesSprintfPlaceholders(): void
    {
        $scraper = new StubScraper(new MockHttpClient());

        $this->assertSame('https://example.test/api/today', $scraper->buildUrl('today'));
    }

    public function testGetUrlAppendsOptionsAsQueryString(): void
    {
        $scraper = new StubScraper(new MockHttpClient(), ['page' => 2, 'secure' => true]);

        $url = $scraper->buildUrl('x');

        $this->assertStringContainsString('https://example.test/api/x?', $url);
        $this->assertStringContainsString('page=2', $url);
        // booleans are normalised to the strings 'true' / 'false'
        $this->assertStringContainsString('secure=true', $url);
    }

    public function testGetUrlDoesNotTreatEncodedQueryValuesAsSprintfPlaceholders(): void
    {
        $scraper = new StubScraper(new MockHttpClient(), ['next' => 'https://example.test/path']);

        $url = $scraper->buildUrl('x');

        $this->assertStringContainsString('next=https%3A%2F%2Fexample.test%2Fpath', $url);
    }

    public function testSetterOptionsAreRoutedToSetMethods(): void
    {
        $scraper = new StubScraper(new MockHttpClient(), ['foo_bar' => 'value', 'page' => 1]);

        // foo_bar maps to setFooBar() and is consumed (not left as a query option)
        $this->assertSame('value', $scraper->fooBar);
        $this->assertArrayNotHasKey('foo_bar', $scraper->options());
        $this->assertArrayHasKey('page', $scraper->options());
    }

    public function testMakeProxyBuildsProxy(): void
    {
        $scraper = new StubScraper(new MockHttpClient());

        $proxy = $scraper->build('1.2.3.4', '8080', 'http');

        $this->assertInstanceOf(Proxy::class, $proxy);
        $this->assertSame('http://1.2.3.4:8080', (string) $proxy);
    }
}

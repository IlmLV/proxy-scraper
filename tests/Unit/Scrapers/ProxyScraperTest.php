<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Scrapers;

use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\Tests\Support\StubScraper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;

class ProxyScraperTest extends TestCase
{
    public function testGetUrlAppliesSprintfPlaceholders(): void
    {
        $scraper = new StubScraper(new MockHttpClient());

        $this->assertSame('https://example.test/api/today', $scraper->buildUrl('today'));
    }

    public function testGetUrlWithoutPlaceholderValuesReturnsUrlVerbatim(): void
    {
        // With no positional values the base URL must not be run through sprintf,
        // so a literal '%' in the URL is preserved instead of being mangled/erroring.
        $scraper = new StubScraper(new MockHttpClient());

        $this->assertSame('https://example.test/api/%s', $scraper->buildUrl());
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

    public function testGetUrlAppendsOptionsWithAmpersandWhenUrlAlreadyHasQuery(): void
    {
        // A source whose URL already carries a query string (e.g. pubproxy.com's
        // "?limit=5&format=json") must not gain a second "?" when options are
        // appended, or the resulting URL is malformed.
        $scraper = new class (new MockHttpClient(), ['country' => 'US']) extends ProxyScraper {
            protected string $url = 'http://example.test/api?limit=5&format=json';

            public function buildUrl(): string
            {
                return $this->getUrl();
            }
        };

        $url = $scraper->buildUrl();

        $this->assertSame('http://example.test/api?limit=5&format=json&country=US', $url);
        $this->assertSame(1, substr_count($url, '?'));
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

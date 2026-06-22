<?php

namespace IlmLV\ProxyScraper\Tests\Unit;

use IlmLV\ProxyScraper\Exceptions\ProxyScraperException;
use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\Sources\ClarketmProxyList;
use IlmLV\ProxyScraper\Sources\FreeProxyListNet;
use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;

class LoadProxiesTest extends TestCase
{
    public function testCollectsProxiesFromSingleSource(): void
    {
        $proxies = LoadProxies::init([], MockClientFactory::fromFixture('Sources/table-http.html'))
            ->only(FreeProxyListNet::class);

        $this->assertCount(2, $proxies->get());
        $this->assertSame([FreeProxyListNet::class => ['http' => 2]], $proxies->stats());
        $this->assertSame([], $proxies->errors());
    }

    public function testCapturesScraperErrors(): void
    {
        $proxies = LoadProxies::init([], MockClientFactory::fromString('', 500))
            ->only(FreeProxyListNet::class);

        $errors = $proxies->errors();

        $this->assertArrayHasKey(FreeProxyListNet::class, $errors);
        $this->assertInstanceOf(ProxyScraperException::class, $errors[FreeProxyListNet::class]);
        $this->assertSame([], $proxies->get());
    }

    public function testRunsMultipleSourcesWithRoutedResponses(): void
    {
        $client = MockClientFactory::router(function (string $method, string $url, array $options): MockResponse {
            if (str_contains($url, 'free-proxy-list')) {
                return new MockResponse(MockClientFactory::load('Sources/table-http.html'));
            }
            return new MockResponse(MockClientFactory::load('Sources/text-list.txt'));
        });

        $proxies = LoadProxies::init([], $client)
            ->only([FreeProxyListNet::class, ClarketmProxyList::class]);

        $this->assertSame(
            [
                FreeProxyListNet::class => ['http' => 2],
                ClarketmProxyList::class => ['http' => 3],
            ],
            $proxies->stats()
        );
        // every proxy from both sources is returned, not just the larger source's worth
        $this->assertCount(5, $proxies->get());
        $this->assertSame([], $proxies->errors());
    }

    public function testReRunningASourceIsIdempotent(): void
    {
        $client = MockClientFactory::router(
            fn (): MockResponse => new MockResponse(MockClientFactory::load('Sources/table-http.html'))
        );

        $loader = LoadProxies::init([], $client)->only(FreeProxyListNet::class);
        $this->assertCount(2, $loader->get());

        // Running again replaces each source's result instead of appending it,
        // so proxies are not accumulated as duplicates.
        $loader->all();
        $this->assertCount(2, $loader->get());
        $this->assertSame([FreeProxyListNet::class => ['http' => 2]], $loader->stats());
    }

    public function testSchedulerIsDueForWildcardSchedule(): void
    {
        $this->assertTrue(LoadProxies::schedulerIsDue('* * * * *'));
    }
}

<?php

namespace IlmLV\ProxyScraper\Tests\Unit;

use IlmLV\ProxyScraper\Exceptions\ProxyScraperException;
use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\Sources\ClarketmProxyList;
use IlmLV\ProxyScraper\Sources\FreeProxyListNet;
use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use Symfony\Component\HttpClient\Response\MockResponse;
use PHPUnit\Framework\TestCase;

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
        $client = MockClientFactory::router(function (string $method, string $url): MockResponse {
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
        $this->assertSame([], $proxies->errors());
    }

    public function testSchedulerIsDueForWildcardSchedule(): void
    {
        $this->assertTrue(LoadProxies::schedulerIsDue('* * * * *'));
    }
}

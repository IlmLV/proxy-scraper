<?php

namespace IlmLV\ProxyScraper\Tests\Live;

use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\Tests\Support\Registry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Hits every registered Source against its real endpoint and asserts it is
 * reachable and still returns at least one parseable proxy. This is the
 * automated dead-provider monitor — it talks to the network and is run as a
 * separate, non-blocking CI job.
 */
#[Group('live')]
class SourcesLiveTest extends TestCase
{
    #[DataProvider('sourceProvider')]
    public function testSourceIsReachableAndReturnsProxies(string $class): void
    {
        $client = HttpClient::create(['timeout' => 30, 'max_duration' => 60]);

        $proxies = LoadProxies::init([], $client)->only($class);

        $errors = $proxies->errors();
        if (isset($errors[$class])) {
            $this->fail($class . ' failed to scrape: ' . $errors[$class]->getMessage());
        }

        $this->assertGreaterThan(
            0,
            count($proxies->get()),
            $class . ' is reachable but returned no proxies (parsing may have drifted)'
        );
    }

    public static function sourceProvider(): array
    {
        $cases = [];
        foreach (Registry::scrapers() as $class) {
            $cases[$class] = [$class];
        }
        return $cases;
    }
}

<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Validations;

use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Validations\Domains\ExampleCom;
use IlmLV\ProxyScraper\Validations\DomainsValidation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DomainsValidationTest extends TestCase
{
    public function testNoValidatorsRunByDefault(): void
    {
        $validation = new DomainsValidation(self::expectedPagesClient());

        $this->assertFalse(isset($validation->{'example.com'}));
        $this->assertSame([], $validation->jsonSerialize());
    }

    public function testConfiguredValidatorRunsAndPasses(): void
    {
        $validation = new DomainsValidation(self::expectedPagesClient(), [ExampleCom::class]);

        $this->assertTrue($validation->{'example.com'}->valid);
    }

    public function testDomainValidatorFailsOnUnexpectedPage(): void
    {
        $client = MockClientFactory::router(
            fn (string $method, string $url, array $options) => new MockResponse('<html><head><title>Blocked</title></head><body></body></html>')
        );

        $validation = new DomainsValidation($client, [ExampleCom::class]);

        $this->assertFalse($validation->{'example.com'}->valid);
    }

    public function testMagicAccessorsExposeValidatorsByDomain(): void
    {
        $validation = new DomainsValidation(self::expectedPagesClient(), [ExampleCom::class]);

        // __isset / __get
        $this->assertTrue(isset($validation->{'example.com'}));
        $this->assertFalse(isset($validation->{'nonexistent.test'}));
        $this->assertNull($validation->{'nonexistent.test'});

        // __set then read back
        $probe = $validation->{'example.com'};
        $validation->{'custom.test'} = $probe;
        $this->assertSame($probe, $validation->{'custom.test'});

        // __unset
        unset($validation->{'example.com'});
        $this->assertFalse(isset($validation->{'example.com'}));
    }

    public function testSerialisesKeyedByDomainName(): void
    {
        $validation = new DomainsValidation(self::expectedPagesClient(), [ExampleCom::class]);

        $decoded = json_decode(json_encode($validation), true);

        $this->assertArrayHasKey('example.com', $decoded);
        $this->assertTrue($decoded['example.com']['valid']);
    }

    private static function expectedPagesClient(): MockHttpClient
    {
        return MockClientFactory::router(function (string $method, string $url, array $options): MockResponse {
            return str_contains($url, 'example.com')
                ? new MockResponse(MockClientFactory::load('Validations/example.html'))
                : new MockResponse('', ['http_code' => 404]);
        });
    }
}

<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Validations;

use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Validations\DomainsValidation;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use PHPUnit\Framework\TestCase;

class DomainsValidationTest extends TestCase
{
    public function testAllDomainValidatorsPassWithExpectedPages(): void
    {
        $validation = new DomainsValidation(self::expectedPagesClient());

        $this->assertTrue($validation->{'example.com'}->valid);
        $this->assertTrue($validation->{'amazon.com'}->valid);
        $this->assertTrue($validation->{'craigslist.org'}->valid);
        $this->assertTrue($validation->{'google.com'}->valid);
        $this->assertTrue($validation->{'ss.com'}->valid);
    }

    public function testDomainValidatorFailsOnUnexpectedPage(): void
    {
        $client = MockClientFactory::router(
            fn () => new MockResponse('<html><head><title>Blocked</title></head><body></body></html>')
        );

        $validation = new DomainsValidation($client);

        $this->assertFalse($validation->{'example.com'}->valid);
    }

    public function testMagicAccessorsExposeValidatorsByDomain(): void
    {
        $validation = new DomainsValidation(self::expectedPagesClient());

        // __isset / __get
        $this->assertTrue(isset($validation->{'amazon.com'}));
        $this->assertFalse(isset($validation->{'nonexistent.test'}));
        $this->assertNull($validation->{'nonexistent.test'});

        // __set then read back
        $probe = $validation->{'example.com'};
        $validation->{'custom.test'} = $probe;
        $this->assertSame($probe, $validation->{'custom.test'});

        // __unset
        unset($validation->{'amazon.com'});
        $this->assertFalse(isset($validation->{'amazon.com'}));
    }

    public function testSerialisesKeyedByDomainName(): void
    {
        $validation = new DomainsValidation(self::expectedPagesClient());

        $decoded = json_decode(json_encode($validation), true);

        $this->assertArrayHasKey('example.com', $decoded);
        $this->assertArrayHasKey('ss.com', $decoded);
        $this->assertTrue($decoded['example.com']['valid']);
    }

    private static function expectedPagesClient(): MockHttpClient
    {
        return MockClientFactory::router(function (string $method, string $url): MockResponse {
            $fixture = match (true) {
                str_contains($url, 'example.com') => 'Validations/example.html',
                str_contains($url, 'amazon')      => 'Validations/amazon.html',
                str_contains($url, 'craigslist')  => 'Validations/craigslist.html',
                str_contains($url, 'google')      => 'Validations/google.html',
                str_contains($url, 'ss.com')      => 'Validations/ss.html',
                default                           => null,
            };

            return $fixture === null
                ? new MockResponse('', ['http_code' => 404])
                : new MockResponse(MockClientFactory::load($fixture));
        });
    }
}

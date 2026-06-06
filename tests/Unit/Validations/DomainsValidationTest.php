<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Validations;

use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Validations\DomainsValidation;
use Symfony\Component\HttpClient\Response\MockResponse;
use PHPUnit\Framework\TestCase;

class DomainsValidationTest extends TestCase
{
    public function testAllDomainValidatorsPassWithExpectedPages(): void
    {
        $client = MockClientFactory::router(function (string $method, string $url): MockResponse {
            $fixture = match (true) {
                str_contains($url, 'example.com')  => 'Validations/example.html',
                str_contains($url, 'amazon')       => 'Validations/amazon.html',
                str_contains($url, 'craigslist')   => 'Validations/craigslist.html',
                str_contains($url, 'google')       => 'Validations/google.html',
                str_contains($url, 'ss.com')       => 'Validations/ss.html',
                default                            => null,
            };

            return $fixture === null
                ? new MockResponse('', ['http_code' => 404])
                : new MockResponse(MockClientFactory::load($fixture));
        });

        $validation = new DomainsValidation($client);

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
}

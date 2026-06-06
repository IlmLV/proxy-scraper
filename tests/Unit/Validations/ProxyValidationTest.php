<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Validations;

use IlmLV\ProxyScraper\Entities\ResponseError;
use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Validations\DomainsValidation;
use IlmLV\ProxyScraper\Validations\IpValidation;
use IlmLV\ProxyScraper\Validations\MethodsValidation;
use IlmLV\ProxyScraper\Validations\ProxyValidation;
use Symfony\Component\HttpClient\Response\MockResponse;
use PHPUnit\Framework\TestCase;

class ProxyValidationTest extends TestCase
{
    public function testFullHappyPathProducesValidResult(): void
    {
        $validation = new ProxyValidation('http://1.2.3.4:8080', self::happyPathClient());

        $this->assertTrue($validation->valid);
        $this->assertSame('elite', $validation->anonymityLevel);

        $this->assertInstanceOf(IpValidation::class, $validation->ip);
        $this->assertTrue($validation->ip->valid);

        $this->assertInstanceOf(MethodsValidation::class, $validation->http);
        $this->assertInstanceOf(MethodsValidation::class, $validation->https);
        $this->assertTrue($validation->http->get->valid);
        $this->assertTrue($validation->https->get->valid);

        $this->assertInstanceOf(DomainsValidation::class, $validation->domains);
        $this->assertTrue($validation->domains->{'example.com'}->valid);

        // timestamp is non-deterministic, assert only its type
        $this->assertInstanceOf(\DateTimeInterface::class, $validation->validatedAt);
    }

    public function testFailureWhenEndpointsUnreachable(): void
    {
        $client = MockClientFactory::router(fn (string $method, string $url, array $options) => new MockResponse('', ['http_code' => 500]));

        $validation = new ProxyValidation('http://1.2.3.4:8080', $client);

        $this->assertFalse($validation->valid);
        $this->assertInstanceOf(ResponseError::class, $validation->error);

        // The result-carrying properties must be safe to read in the failure
        // state, not throw "typed property must not be accessed before
        // initialization".
        $this->assertNull($validation->anonymityLevel);
        $this->assertNull($validation->ip);
        $this->assertNull($validation->http);
        $this->assertNull($validation->https);
        $this->assertNull($validation->domains);
    }

    /**
     * Routes the ~22 requests ProxyValidation makes to appropriate fixtures,
     * regardless of order:
     *  - whoami + proxy:false  -> the real (direct) IP
     *  - whoami otherwise      -> echo {method}, so anonymity reads "elite" and
     *                             every MethodsValidation request validates
     *  - ip.serviss.it         -> reports the proxy host IP (matches 1.2.3.4)
     *  - domain checks         -> the expected per-domain landing pages
     */
    private static function happyPathClient(): \Symfony\Component\HttpClient\MockHttpClient
    {
        return MockClientFactory::router(function (string $method, string $url, array $options): MockResponse {
            if (str_contains($url, 'whoami')) {
                if (($options['proxy'] ?? null) === false) {
                    return new MockResponse(MockClientFactory::load('Validations/realip.json'));
                }
                if ($method === 'HEAD') {
                    return new MockResponse('', ['http_code' => 200]);
                }
                return new MockResponse(json_encode(['method' => $method]), ['http_code' => 200]);
            }
            if (str_contains($url, 'ip.serviss.it')) {
                return new MockResponse(MockClientFactory::load('Validations/ip-match.json'));
            }

            $fixture = match (true) {
                str_contains($url, 'example.com') => 'Validations/example.html',
                str_contains($url, 'amazon')      => 'Validations/amazon.html',
                str_contains($url, 'craigslist')  => 'Validations/craigslist.html',
                str_contains($url, 'google')      => 'Validations/google.html',
                str_contains($url, 'ss.com')      => 'Validations/ss.html',
                default                           => null,
            };

            return $fixture === null
                ? new MockResponse('{}', ['http_code' => 200])
                : new MockResponse(MockClientFactory::load($fixture));
        });
    }
}

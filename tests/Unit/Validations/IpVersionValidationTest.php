<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Validations;

use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Validations\IpVersionValidation;
use Symfony\Component\HttpClient\Response\MockResponse;
use PHPUnit\Framework\TestCase;

class IpVersionValidationTest extends TestCase
{
    public function testBothFamiliesReachable(): void
    {
        $client = MockClientFactory::router(function (string $method, string $url, array $options): MockResponse {
            $ip = str_contains($url, 'ipv6') ? '2001:db8::1' : '203.0.113.4';
            return new MockResponse(json_encode(['ip' => $ip]), ['http_code' => 200]);
        });

        $validation = new IpVersionValidation($client);

        $this->assertTrue($validation->ipv4->valid);
        $this->assertTrue($validation->ipv6->valid);
        $this->assertSame('203.0.113.4', $validation->ipv4->ip);
        $this->assertSame('2001:db8::1', $validation->ipv6->ip);
    }

    public function testIpv4OnlyProxy(): void
    {
        $client = MockClientFactory::router(function (string $method, string $url, array $options): MockResponse {
            if (str_contains($url, 'ipv6')) {
                return new MockResponse('', ['http_code' => 500]);
            }
            return new MockResponse(json_encode(['ip' => '203.0.113.4']), ['http_code' => 200]);
        });

        $validation = new IpVersionValidation($client);

        $this->assertTrue($validation->ipv4->valid);
        $this->assertSame('203.0.113.4', $validation->ipv4->ip);
        $this->assertFalse($validation->ipv6->valid);
        $this->assertNull($validation->ipv6->ip);
    }

    public function testTransportFailureMarksFamilyInvalid(): void
    {
        $client = MockClientFactory::router(function (string $method, string $url, array $options): MockResponse {
            return new MockResponse('', ['error' => 'Could not connect to proxy']);
        });

        $validation = new IpVersionValidation($client);

        $this->assertFalse($validation->ipv4->valid);
        $this->assertFalse($validation->ipv6->valid);
    }
}

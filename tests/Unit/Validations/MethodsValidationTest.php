<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Validations;

use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Validations\HeadersValidation;
use IlmLV\ProxyScraper\Validations\MethodsValidation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;

class MethodsValidationTest extends TestCase
{
    public function testAllMethodsValidatedAndLatencyAveraged(): void
    {
        // echo the request method back (HEAD must answer with an empty 200 body)
        $client = MockClientFactory::router(function (string $method, string $url, array $options): MockResponse {
            if ($method === 'HEAD') {
                return new MockResponse('', ['http_code' => 200]);
            }
            return new MockResponse(json_encode(['method' => $method]), ['http_code' => 200]);
        });

        $validation = new MethodsValidation('http://whoami.serviss.it/?format=json', $client);

        foreach (['get', 'post', 'put', 'options', 'head', 'delete', 'patch'] as $method) {
            $this->assertInstanceOf(HeadersValidation::class, $validation->{$method});
            $this->assertTrue($validation->{$method}->valid, $method . ' should validate');
        }
        $this->assertIsFloat($validation->latency);
    }

    public function testCustomRequestMethodsSubset(): void
    {
        $client = MockClientFactory::router(function (string $method, string $url, array $options): MockResponse {
            return new MockResponse(json_encode(['method' => $method]), ['http_code' => 200]);
        });

        $validation = new MethodsValidation('http://whoami.serviss.it/?format=json', $client, ['GET']);

        $this->assertInstanceOf(HeadersValidation::class, $validation->get);
        $this->assertTrue($validation->get->valid);

        // Methods that were not requested are simply absent — reading them returns
        // null rather than hitting an uninitialised typed property.
        $this->assertFalse(isset($validation->post));
        $this->assertNull($validation->post);

        // JSON keeps the historical "latency first, then one entry per method" shape.
        $json = json_decode(json_encode($validation), true);
        $this->assertSame(['latency', 'get'], array_keys($json));
    }
}

<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Validations;

use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Validations\HeadersValidation;
use IlmLV\ProxyScraper\Validations\MethodsValidation;
use Symfony\Component\HttpClient\Response\MockResponse;
use PHPUnit\Framework\TestCase;

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
}

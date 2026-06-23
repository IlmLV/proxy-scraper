<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Validations;

use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Validations\HeadersValidation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class HeadersValidationTest extends TestCase
{
    public function testDecodesGzipCompressedResponse(): void
    {
        // We send Accept-Encoding: gzip, so a real server may gzip the echo
        // response and the HttpClient hands it back compressed. The validation
        // must decode it before parsing, otherwise json_decode fails and the
        // proxy is wrongly marked as not forwarding HTTP.
        $body = gzencode(MockClientFactory::load('Validations/headers-echo-get.json'));

        $validation = new HeadersValidation(
            'GET',
            'http://whoami.serviss.it/?format=json',
            new MockHttpClient(new MockResponse($body, [
                'http_code'        => 200,
                'response_headers' => ['content-encoding' => 'gzip'],
            ]))
        );

        $this->assertTrue($validation->valid);
        $this->assertTrue($validation->headers['Accept']);
        $this->assertTrue($validation->headers['X-Real-Ip']);
    }

    public function testValidWhenEchoedMethodMatchesAndHeadersPreserved(): void
    {
        $validation = new HeadersValidation(
            'GET',
            'http://whoami.serviss.it/?format=json',
            MockClientFactory::fromFixture('Validations/headers-echo-get.json')
        );

        $this->assertTrue($validation->valid);
        $this->assertIsFloat($validation->latency);
        // headers echoed back with matching values are flagged true
        $this->assertTrue($validation->headers['Accept']);
        $this->assertTrue($validation->headers['X-Real-Ip']);
        // a header the echo did not return is flagged false
        $this->assertFalse($validation->headers['Pragma']);
    }

    public function testHeadRequestValidWhenBodyEmptyAndStatusOk(): void
    {
        $validation = new HeadersValidation(
            'HEAD',
            'http://whoami.serviss.it/?format=json',
            MockClientFactory::fromString('', 200)
        );

        $this->assertTrue($validation->valid);
    }

    public function testInvalidWhenResponseMissingMethodKey(): void
    {
        // A 200 response whose JSON omits the echoed "method" key must be reported
        // invalid without raising an "undefined array key" warning (the suite runs
        // with failOnWarning enabled).
        $validation = new HeadersValidation(
            'GET',
            'http://whoami.serviss.it/?format=json',
            MockClientFactory::fromString('{"accept":"application/json"}', 200)
        );

        $this->assertFalse($validation->valid);
    }

    public function testInvalidWhenRequestFails(): void
    {
        $validation = new HeadersValidation(
            'GET',
            'http://whoami.serviss.it/?format=json',
            MockClientFactory::fromString('', 500)
        );

        $this->assertFalse($validation->valid);
    }
}

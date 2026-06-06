<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Validations;

use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Validations\HeadersValidation;
use PHPUnit\Framework\TestCase;

class HeadersValidationTest extends TestCase
{
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

<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Validations;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Validations\IpValidation;
use PHPUnit\Framework\TestCase;

class IpValidationTest extends TestCase
{
    public function testValidWhenReportedIpMatchesProxyHost(): void
    {
        $validation = new IpValidation(
            new Host('1.2.3.4'),
            MockClientFactory::fromFixture('Validations/ip-match.json')
        );

        $this->assertTrue($validation->valid);
        $this->assertSame('US', $validation->countryIsoCode);
        $this->assertSame('Example Hosting', $validation->organisation);
    }

    public function testInvalidWhenReportedIpDiffersFromProxyHost(): void
    {
        $validation = new IpValidation(
            new Host('1.2.3.4'),
            MockClientFactory::fromFixture('Validations/ip-mismatch.json')
        );

        $this->assertFalse($validation->valid);
    }

    public function testResultPropertiesAreNullWhenRequestFails(): void
    {
        // Reading the result/latency properties on a failed validation must not
        // throw "must not be accessed before initialization"; they read as null.
        $validation = new IpValidation(
            new Host('1.2.3.4'),
            MockClientFactory::fromString('', 500)
        );

        $this->assertFalse($validation->valid);
        $this->assertNull($validation->countryIsoCode);
        $this->assertNull($validation->organisation);
        $this->assertNull($validation->latency);
        $this->assertNotNull($validation->error);
    }
}

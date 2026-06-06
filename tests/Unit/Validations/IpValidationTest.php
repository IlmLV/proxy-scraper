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
}

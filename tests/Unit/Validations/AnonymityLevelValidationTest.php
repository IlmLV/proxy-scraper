<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Validations;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Validations\AnonymityLevelValidation;
use PHPUnit\Framework\TestCase;

class AnonymityLevelValidationTest extends TestCase
{
    public function testEliteWhenNoProxyHeadersAndRealIpHidden(): void
    {
        $validation = AnonymityLevelValidation::make(
            new Host('100.64.0.1'),
            MockClientFactory::fromFixture('Validations/anonymity-elite.json')
        )->run();

        $this->assertSame('elite', $validation->anonymityLevel);
        $this->assertTrue($validation->valid);
    }

    public function testAnonymousWhenProxyHeaderPresent(): void
    {
        $validation = AnonymityLevelValidation::make(
            new Host('100.64.0.1'),
            MockClientFactory::fromFixture('Validations/anonymity-anonymous.json')
        )->run();

        $this->assertSame('anonymous', $validation->anonymityLevel);
        $this->assertTrue($validation->valid);
    }

    public function testExposedWhenRealIpLeaks(): void
    {
        $validation = AnonymityLevelValidation::make(
            new Host('100.64.0.1'),
            MockClientFactory::fromFixture('Validations/anonymity-exposed.json')
        )->run();

        $this->assertSame('exposed', $validation->anonymityLevel);
        // An exposed proxy leaks the real IP, so the check itself does not pass.
        $this->assertFalse($validation->valid);
    }

    public function testNullAndErrorOnVerificationWall(): void
    {
        $validation = AnonymityLevelValidation::make(
            new Host('100.64.0.1'),
            MockClientFactory::fromFixture('Validations/anonymity-verify.html')
        )->run();

        $this->assertNull($validation->anonymityLevel);
        $this->assertFalse($validation->valid);
        $this->assertNotEmpty((string) $validation->error);
    }
}

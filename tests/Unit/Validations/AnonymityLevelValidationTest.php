<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Validations;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Exceptions\ValidatorException;
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
        $this->assertSame('elite', (string) $validation);
    }

    public function testAnonymousWhenProxyHeaderPresent(): void
    {
        $validation = AnonymityLevelValidation::make(
            new Host('100.64.0.1'),
            MockClientFactory::fromFixture('Validations/anonymity-anonymous.json')
        )->run();

        $this->assertSame('anonymous', $validation->anonymityLevel);
    }

    public function testExposedWhenRealIpLeaks(): void
    {
        $validation = AnonymityLevelValidation::make(
            new Host('100.64.0.1'),
            MockClientFactory::fromFixture('Validations/anonymity-exposed.json')
        )->run();

        $this->assertSame('exposed', $validation->anonymityLevel);
    }

    public function testNullAndErrorOnVerificationWall(): void
    {
        $validation = AnonymityLevelValidation::make(
            new Host('100.64.0.1'),
            MockClientFactory::fromFixture('Validations/anonymity-verify.html')
        )->run();

        $this->assertNull($validation->anonymityLevel);
        $this->assertNotEmpty((string) $validation->error);

        $this->expectException(ValidatorException::class);
        (string) $validation;
    }
}

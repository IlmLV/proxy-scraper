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
        $validation = new AnonymityLevelValidation(
            new Host('100.64.0.1'),
            MockClientFactory::fromFixture('Validations/anonymity-elite.json')
        );

        $this->assertSame('elite', $validation->anonymityLevel);
        $this->assertSame('elite', (string) $validation);
    }

    public function testAnonymousWhenProxyHeaderPresent(): void
    {
        $validation = new AnonymityLevelValidation(
            new Host('100.64.0.1'),
            MockClientFactory::fromFixture('Validations/anonymity-anonymous.json')
        );

        $this->assertSame('anonymous', $validation->anonymityLevel);
    }

    public function testExposedWhenRealIpLeaks(): void
    {
        $validation = new AnonymityLevelValidation(
            new Host('100.64.0.1'),
            MockClientFactory::fromFixture('Validations/anonymity-exposed.json')
        );

        $this->assertSame('exposed', $validation->anonymityLevel);
    }

    public function testNullAndErrorOnVerificationWall(): void
    {
        $validation = new AnonymityLevelValidation(
            new Host('100.64.0.1'),
            MockClientFactory::fromFixture('Validations/anonymity-verify.html')
        );

        $this->assertNull($validation->anonymityLevel);
        $this->assertNotEmpty((string) $validation->error);

        $this->expectException(ValidatorException::class);
        (string) $validation;
    }
}

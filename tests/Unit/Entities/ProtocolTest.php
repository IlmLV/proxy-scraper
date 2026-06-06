<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Entities;

use IlmLV\ProxyScraper\Entities\Protocol;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProtocolTest extends TestCase
{
    #[DataProvider('allowedProvider')]
    public function testAllowedProtocols(string $protocol): void
    {
        $this->assertSame($protocol, (string) new Protocol($protocol));
    }

    public static function allowedProvider(): array
    {
        return array_map(fn ($p) => [$p], Protocol::ALLOWED_PROTOCOLS);
    }

    #[DataProvider('invalidProvider')]
    public function testUnknownProtocolsThrow(string $protocol): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Protocol($protocol);
    }

    public static function invalidProvider(): array
    {
        return [['ftp'], ['HTTP'], ['socks'], ['']];
    }
}

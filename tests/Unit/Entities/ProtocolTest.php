<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Entities;

use IlmLV\ProxyScraper\Entities\Protocol;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProtocolTest extends TestCase
{
    #[DataProvider('allowedProvider')]
    public function testAllowedProtocolsResolveToTheirCase(string $protocol): void
    {
        $this->assertSame($protocol, Protocol::fromString($protocol)->value);
    }

    public static function allowedProvider(): array
    {
        return array_map(fn (Protocol $p) => [$p->value], Protocol::cases());
    }

    public function testExposesValueAndResolvesByString(): void
    {
        $this->assertSame('socks5', Protocol::Socks5->value);
        $this->assertSame(Protocol::Socks5, Protocol::fromString('socks5'));
    }

    #[DataProvider('invalidProvider')]
    public function testUnknownProtocolsThrow(string $protocol): void
    {
        $this->expectException(InvalidArgumentException::class);
        Protocol::fromString($protocol);
    }

    public static function invalidProvider(): array
    {
        return [['ftp'], ['HTTP'], ['socks'], ['']];
    }
}

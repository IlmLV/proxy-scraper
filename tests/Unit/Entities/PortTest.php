<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Entities;

use IlmLV\ProxyScraper\Entities\Port;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PortTest extends TestCase
{
    #[DataProvider('validProvider')]
    public function testValidPorts(int $port): void
    {
        $this->assertSame((string) $port, (string) new Port($port));
    }

    public static function validProvider(): array
    {
        return [[1], [80], [8080], [65535]];
    }

    public function testExposesTypedValue(): void
    {
        $this->assertSame(8080, (new Port('8080'))->value);
    }

    #[DataProvider('invalidProvider')]
    public function testOutOfRangePortsThrow(int $port): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Port($port);
    }

    public static function invalidProvider(): array
    {
        return [[0], [-1], [65536], [99999]];
    }
}

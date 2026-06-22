<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Entities;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HostTest extends TestCase
{
    public function testIpv4LiteralResolvesToItself(): void
    {
        $host = new Host('1.2.3.4');

        $this->assertSame('1.2.3.4', $host->host);
        // IP literals never trigger a real DNS lookup.
        $this->assertSame('1.2.3.4', $host->ip());
        $this->assertSame('1.2.3.4', (string) $host);
    }

    public function testIpv6LiteralResolvesToItself(): void
    {
        $host = new Host('2001:db8::1');

        $this->assertSame('2001:db8::1', $host->host);
        $this->assertSame('2001:db8::1', $host->ip());
    }

    public function testUnresolvableDomainReturnsNullIp(): void
    {
        // ".invalid" is reserved (RFC 2606) and never resolves, so ip() reports
        // null instead of echoing the domain back as if it were an address.
        $host = new Host('definitely-not-real.invalid');

        $this->assertSame('definitely-not-real.invalid', $host->host);
        $this->assertNull($host->ip());
    }

    #[DataProvider('invalidProvider')]
    public function testInvalidHostsThrow(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Host($value);
    }

    public static function invalidProvider(): array
    {
        // Host only rejects what fails IPv4 *and* IPv6 *and* the (lax)
        // FILTER_VALIDATE_DOMAIN check — e.g. empty, empty labels, over-length.
        return [
            'empty' => [''],
            'bare dot' => ['.'],
            'empty label' => ['a..b'],
            'over length' => [str_repeat('a', 300)],
        ];
    }
}

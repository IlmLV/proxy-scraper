<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Entities;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Entities\Port;
use IlmLV\ProxyScraper\Entities\Protocol;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProxyTest extends TestCase
{
    public function testParsesPlainProxyString(): void
    {
        $proxy = new Proxy('http://1.2.3.4:8080');

        $this->assertSame('http', (string) $proxy->protocol);
        $this->assertSame('1.2.3.4', (string) $proxy->host);
        $this->assertSame('8080', (string) $proxy->port);
        $this->assertNull($proxy->username);
        $this->assertNull($proxy->password);
        $this->assertSame('http://1.2.3.4:8080', (string) $proxy);
    }

    public function testParsesProxyStringWithCredentials(): void
    {
        $proxy = new Proxy('socks5://user:pass@1.2.3.4:1080');

        $this->assertSame('socks5', (string) $proxy->protocol);
        $this->assertSame('1.2.3.4', (string) $proxy->host);
        $this->assertSame('1080', (string) $proxy->port);
        $this->assertSame('user', $proxy->username);
        $this->assertSame('pass', $proxy->password);
        $this->assertSame('socks5://user:pass@1.2.3.4:1080', (string) $proxy);
    }

    public function testParsesBracketedIpv6Host(): void
    {
        $proxy = new Proxy('http://[2001:db8::1]:8080');

        $this->assertSame('http', (string) $proxy->protocol);
        $this->assertSame('2001:db8::1', (string) $proxy->host);
        $this->assertSame('8080', (string) $proxy->port);
        // IPv6 hosts are re-bracketed so the string round-trips back through parse().
        $this->assertSame('http://[2001:db8::1]:8080', (string) $proxy);
    }

    public function testParsesPasswordContainingColonAndAt(): void
    {
        $proxy = new Proxy('socks5://user:p@ss:word@1.2.3.4:1080');

        $this->assertSame('user', $proxy->username);
        $this->assertSame('p@ss:word', $proxy->password);
        $this->assertSame('1.2.3.4', (string) $proxy->host);
        $this->assertSame('1080', (string) $proxy->port);
        $this->assertSame('socks5://user:p@ss:word@1.2.3.4:1080', (string) $proxy);
    }

    public function testBuildsFromEntities(): void
    {
        $proxy = new Proxy(new Protocol('https'), new Host('1.2.3.4'), new Port(443));

        $this->assertSame('https://1.2.3.4:443', (string) $proxy);
    }

    #[DataProvider('invalidStringProvider')]
    public function testInvalidStringsThrow(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Proxy($value);
    }

    public static function invalidStringProvider(): array
    {
        return [
            'no protocol separator' => ['1.2.3.4:8080'],
            'no port' => ['http://1.2.3.4'],
            'bad credentials format' => ['http://useronly@1.2.3.4:8080'],
            'unknown protocol' => ['ftp://1.2.3.4:8080'],
            'unbracketed ipv6 is ambiguous' => ['http://2001:db8::1:8080'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Reports the proxy's egress capability per IP address family by requesting an
 * IPv4-only host and an IPv6-only host through the proxy. A proxy may reach
 * IPv4-only destinations, IPv6-only destinations, both, or neither.
 *
 * The endpoints are served on A-record-only / AAAA-record-only hostnames, so
 * an HTTP CONNECT or SOCKS5h proxy (which resolves the hostname itself) is
 * forced to open a connection of the matching address family.
 */
class IpVersionValidation
{
    const IPV4_URL = 'http://ipv4.serviss.it/?format=json';
    const IPV6_URL = 'http://ipv6.serviss.it/?format=json';

    public EgressValidation $ipv4;
    public EgressValidation $ipv6;

    /**
     * @param HttpClientInterface|null $client
     * @param string|null $ipv4Url
     * @param string|null $ipv6Url
     */
    public function __construct(?HttpClientInterface $client = null, ?string $ipv4Url = null, ?string $ipv6Url = null)
    {
        $this->ipv4 = new EgressValidation($ipv4Url ?? self::IPV4_URL, $client);
        $this->ipv6 = new EgressValidation($ipv6Url ?? self::IPV6_URL, $client);
    }
}

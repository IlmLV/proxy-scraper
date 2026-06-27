<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

use JsonSerializable;
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
class IpVersionValidation implements JsonSerializable, ValidationInterface
{
    public const IPV4_URL = ValidationEndpoints::IPV4;
    public const IPV6_URL = ValidationEndpoints::IPV6;

    public ?EgressValidation $ipv4 = null;
    public ?EgressValidation $ipv6 = null;

    private ?HttpClientInterface $client;
    private string $ipv4Url = self::IPV4_URL;
    private string $ipv6Url = self::IPV6_URL;

    public function __construct(?HttpClientInterface $client = null)
    {
        $this->client = $client;
    }

    public static function make(?HttpClientInterface $client = null): self
    {
        return new self($client);
    }

    /**
     * Override the IPv4-only probe endpoint (defaults to ValidationEndpoints::IPV4).
     * Set before run().
     */
    public function setIpv4Url(string $url): self
    {
        $this->ipv4Url = $url;

        return $this;
    }

    /**
     * Override the IPv6-only probe endpoint (defaults to ValidationEndpoints::IPV6).
     * Set before run().
     */
    public function setIpv6Url(string $url): self
    {
        $this->ipv6Url = $url;

        return $this;
    }

    /**
     * Probe egress for each address family (populating $ipv4 / $ipv6) and return
     * $this. Construction performs no I/O.
     */
    public function run(): self
    {
        $this->ipv4 = EgressValidation::make($this->ipv4Url, $this->client)->run();
        $this->ipv6 = EgressValidation::make($this->ipv6Url, $this->client)->run();

        return $this;
    }

    /**
     * @return array<string, EgressValidation|null>
     */
    public function jsonSerialize(): array
    {
        return ['ipv4' => $this->ipv4, 'ipv6' => $this->ipv6];
    }
}

<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Entities;

use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;

final class Host
{
    public readonly string $host;

    private bool $resolved = false;

    private ?string $ip = null;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $host)
    {
        // An IP literal is already its own address — no resolution needed.
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            || filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
        ) {
            $this->host = $host;
            $this->ip = $host;
            $this->resolved = true;

            return;
        }

        if (!filter_var($host, FILTER_VALIDATE_DOMAIN)) {
            throw new InvalidArgumentException('Invalid ipv4|ipv6|domain string: ' . $host);
        }

        $this->host = $host;
    }

    /**
     * Resolve the host to an IP address, or null when it cannot be resolved.
     *
     * IP literals return themselves. Domains are resolved (IPv4, via DNS) on the
     * first call — this performs network I/O — and the result is memoised. Returns
     * null when resolution fails rather than silently echoing the domain back.
     */
    public function ip(): ?string
    {
        if ($this->resolved) {
            return $this->ip;
        }

        // gethostbyname() returns its input unchanged when resolution fails; the
        // failure is handled here, so its E_WARNING is suppressed deliberately.
        $resolved = @gethostbyname($this->host);
        $this->ip = $resolved === $this->host ? null : $resolved;
        $this->resolved = true;

        return $this->ip;
    }

    public function __toString(): string
    {
        return $this->host;
    }
}

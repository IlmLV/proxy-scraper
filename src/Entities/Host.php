<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Entities;

use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;

final class Host
{
    public readonly string $host;

    public readonly string $ip;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $host)
    {
        // An IP literal is already its own address — resolve nothing.
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            || filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
        ) {
            $this->host = $host;
            $this->ip = $host;

            return;
        }

        if (!filter_var($host, FILTER_VALIDATE_DOMAIN)) {
            throw new InvalidArgumentException('Invalid ipv4|ipv6|domain string: ' . $host);
        }

        $this->host = $host;
        $this->ip = gethostbyname($host);
    }

    public function __toString(): string
    {
        return $this->host;
    }
}

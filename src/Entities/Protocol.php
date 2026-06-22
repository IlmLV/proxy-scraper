<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Entities;

use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;

final class Protocol
{
    public const ALLOWED_PROTOCOLS = [
        'http',
        'https',
        'socks4',
        'socks5',
    ];

    private string $protocol;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $protocol)
    {
        if (!in_array($protocol, self::ALLOWED_PROTOCOLS)) {
            throw new InvalidArgumentException('Unknown protocol: ' . $protocol);
        }
        $this->protocol = $protocol;
    }

    public function __toString(): string
    {
        return $this->protocol;
    }
}

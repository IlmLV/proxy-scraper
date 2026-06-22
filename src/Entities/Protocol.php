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

    public readonly string $value;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $protocol)
    {
        if (!in_array($protocol, self::ALLOWED_PROTOCOLS, true)) {
            throw new InvalidArgumentException('Unknown protocol: ' . $protocol);
        }
        $this->value = $protocol;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Entities;

use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;

enum Protocol: string
{
    case Http = 'http';
    case Https = 'https';
    case Socks4 = 'socks4';
    case Socks5 = 'socks5';

    /**
     * Resolve a protocol name to its case, throwing the library's own
     * {@see InvalidArgumentException} (rather than the native \ValueError) on an
     * unknown value, so callers keep catching a single exception type.
     *
     * @throws InvalidArgumentException
     */
    public static function fromString(string $protocol): self
    {
        return self::tryFrom($protocol)
            ?? throw new InvalidArgumentException(sprintf(
                'Unknown protocol "%s"; expected one of: %s',
                $protocol,
                implode(', ', array_column(self::cases(), 'value')),
            ));
    }
}

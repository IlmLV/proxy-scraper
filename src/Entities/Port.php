<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Entities;

use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;

final class Port
{
    private const MIN_PORT_NUMBER = 1;
    private const MAX_PORT_NUMBER = 65535;

    public readonly int $value;

    /**
     * @param int|string $port Accepts a numeric string (as produced by proxy-string
     *                         parsing and most scraped sources) or an integer.
     * @throws InvalidArgumentException
     */
    public function __construct(int|string $port)
    {
        if (is_string($port) && !ctype_digit($port)) {
            throw new InvalidArgumentException('Bad port number: ' . $port);
        }
        $port = (int) $port;

        if (self::MIN_PORT_NUMBER > $port) {
            throw new InvalidArgumentException('Bad port number: ' . $port);
        }
        if (self::MAX_PORT_NUMBER < $port) {
            throw new InvalidArgumentException('Bad port number: ' . $port);
        }
        $this->value = $port;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}

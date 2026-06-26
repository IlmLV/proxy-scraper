<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Entities;

use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;

final class Proxy
{
    public readonly Protocol $protocol;

    public readonly Host $host;

    public readonly Port $port;

    public readonly ?string $username;

    public readonly ?string $password;

    public function __construct(Protocol $protocol, Host $host, Port $port, ?string $username = null, ?string $password = null)
    {
        $this->protocol = $protocol;
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Build a Proxy from a "protocol://[user:pass@]host:port" string.
     * IPv6 hosts must be bracketed, e.g. "http://[::1]:8080".
     *
     * @throws InvalidArgumentException
     */
    public static function fromString(string $proxy): self
    {
        $protocolAddress = explode('://', $proxy, 2);
        if (count($protocolAddress) !== 2) {
            throw new InvalidArgumentException('Bad formatted proxy string, no protocol found');
        }
        [$protocol, $remainder] = $protocolAddress;

        $username = null;
        $password = null;

        // Credentials, when present, sit before the last "@" (an unencoded "@" may
        // appear in the password); the password itself may contain ":", so the
        // credentials are split on the first ":" only.
        $atPosition = strrpos($remainder, '@');
        if ($atPosition !== false) {
            $credentials = substr($remainder, 0, $atPosition);
            $address = substr($remainder, $atPosition + 1);

            $colonPosition = strpos($credentials, ':');
            if ($colonPosition === false) {
                throw new InvalidArgumentException('Bad formatted proxy string, invalid credentials format');
            }
            $username = substr($credentials, 0, $colonPosition);
            $password = substr($credentials, $colonPosition + 1);
        } else {
            $address = $remainder;
        }

        [$host, $port] = self::splitHostPort($address);

        return new self(
            Protocol::fromString($protocol),
            new Host($host),
            new Port($port),
            $username,
            $password,
        );
    }

    /**
     * Split a "host:port" address into its parts, supporting bracketed IPv6
     * literals ("[::1]:8080"). The host is returned without brackets.
     *
     * @return array{0: string, 1: string}
     * @throws InvalidArgumentException
     */
    private static function splitHostPort(string $address): array
    {
        if (str_starts_with($address, '[')) {
            $closing = strpos($address, ']');
            if ($closing === false || ($address[$closing + 1] ?? '') !== ':') {
                throw new InvalidArgumentException('Bad formatted proxy string, no port found');
            }

            return [substr($address, 1, $closing - 1), substr($address, $closing + 2)];
        }

        // The port follows the last ":"; reject a missing port or an unbracketed
        // IPv6 literal (more than one colon), which is ambiguous.
        $colonPosition = strrpos($address, ':');
        if ($colonPosition === false || strpos($address, ':') !== $colonPosition) {
            throw new InvalidArgumentException('Bad formatted proxy string, no port found');
        }

        return [substr($address, 0, $colonPosition), substr($address, $colonPosition + 1)];
    }

    public function __toString(): string
    {
        $host = (string) $this->host;
        // Re-bracket IPv6 literals so the result round-trips back through fromString().
        if (str_contains($host, ':')) {
            $host = '[' . $host . ']';
        }

        return $this->protocol->value . '://'
            . ($this->username ? $this->username . ($this->password ? ':' . $this->password : '') . '@' : '')
            . $host . ':' . $this->port;
    }
}

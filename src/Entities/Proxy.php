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

    /**
     * Construct from value objects, or pass a single proxy string
     * ("protocol://[user:pass@]host:port") to have it parsed.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Protocol|string $mixed, ?Host $host = null, ?Port $port = null, ?string $username = null, ?string $password = null)
    {
        if ($mixed instanceof Protocol) {
            if ($host === null || $port === null) {
                throw new InvalidArgumentException('Host and port are required when constructing from a Protocol instance');
            }
            $this->protocol = $mixed;
            $this->host = $host;
            $this->port = $port;
            $this->username = $username;
            $this->password = $password;

            return;
        }

        $parsed = self::parse($mixed);
        $this->protocol = $parsed['protocol'];
        $this->host = $parsed['host'];
        $this->port = $parsed['port'];
        $this->username = $parsed['username'];
        $this->password = $parsed['password'];
    }

    /**
     * Parse a "protocol://[user:pass@]host:port" string into its components.
     *
     * @return array{protocol: Protocol, host: Host, port: Port, username: ?string, password: ?string}
     * @throws InvalidArgumentException
     */
    private static function parse(string $proxy): array
    {
        $protocolIpPort = explode('://', $proxy);
        if (count($protocolIpPort) !== 2) {
            throw new InvalidArgumentException('Bad formatted proxy string, no protocol found');
        }

        $username = null;
        $password = null;

        $authAddress = explode('@', $protocolIpPort[1]);
        if (count($authAddress) === 2) {
            $address = $authAddress[1];

            $usernamePassword = explode(':', $authAddress[0]);
            if (count($usernamePassword) !== 2) {
                throw new InvalidArgumentException('Bad formatted proxy string, invalid credentials format');
            }
            $username = $usernamePassword[0];
            $password = $usernamePassword[1];
        } else {
            $address = $protocolIpPort[1];
        }

        $ipPort = explode(':', $address);
        if (count($ipPort) !== 2) {
            throw new InvalidArgumentException('Bad formatted proxy string, no port found');
        }

        return [
            'protocol' => new Protocol($protocolIpPort[0]),
            'host' => new Host($ipPort[0]),
            'port' => new Port($ipPort[1]),
            'username' => $username,
            'password' => $password,
        ];
    }

    public function __toString(): string
    {
        return $this->protocol . '://'
            . ($this->username ? $this->username . ($this->password ? ':' . $this->password : '') . '@' : '')
            . $this->host . ':' . $this->port;
    }
}

<?php

namespace IlmLV\ProxyScraper\Entities;

use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;

final class Proxy
{
    /**
     * @var Protocol
     */
    public Protocol $protocol;

    /**
     * @var Host
     */
    public Host $host;

    /**
     * @var Port
     */
    public Port $port;

    /**
     * @var ?string
     */
    public ?string $username = null;

    /**
     * @var ?string
     */
    public ?string $password = null;

    /**
     * @param Protocol|string $mixed
     * @param Host|null $host
     * @param Port|null $port
     * @param ?string $username
     * @param ?string $password
     * @throws InvalidArgumentException
     */
    public function __construct(Protocol|string $mixed, Host $host = null, Port $port = null, string $username = null, string $password = null)
    {
        if ($mixed instanceof Protocol) {
            $this->protocol = $mixed;
            $this->host = $host;
            $this->port = $port;
            $this->username = $username;
            $this->password = $password;
        }
        else {
            $protocolIpPort = explode('://', $mixed);
            if (count($protocolIpPort) !== 2) {
                throw new InvalidArgumentException('Bad formatted proxy string, no protocol found');
            }
            else {
                $authAddress = explode('@', $protocolIpPort[1]);
                if (count($authAddress) == 2) {
                    $address = $authAddress[1];

                    $usernamePassword = explode(':', $authAddress[0]);
                    if (count($usernamePassword) == 2) {
                        $this->username = $usernamePassword[0];
                        $this->password = $usernamePassword[1];
                    }
                    else {
                        throw new InvalidArgumentException('Bad formatted proxy string, invalid credentials format');
                    }
                } else {
                    $address = $protocolIpPort[1];
                }

                $ipPort = explode(':', $address);
                if (count($ipPort) !== 2) {
                    throw new InvalidArgumentException('Bad formatted proxy string, no port found');
                }
                else {
                    $this->protocol = new Protocol($protocolIpPort[0]);
                    $this->host = new Host($ipPort[0]);
                    $this->port = new Port($ipPort[1]);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->protocol . '://'
            . ($this->username ? $this->username . ($this->password ? ':' . $this->password : '') . '@' : '')
            . $this->host . ':' . $this->port;
    }
}
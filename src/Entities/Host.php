<?php

namespace IlmLV\ProxyScraper\Entities;

use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;

final class Host
{
    /**
     * @var string
     */
    public string $host;

    /**
     * @var string
     */
    public string $ip;

    /**
     * @param string $host
     * @throws InvalidArgumentException
     */
    public function __construct(string $host)
    {
        if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            && !filter_var($host, FILTER_VALIDATE_DOMAIN)
        ) {
            throw new InvalidArgumentException('Invalid ipv4|ipv6|domain string: ' . $host);
        }
        $this->host = $host;

        if (filter_var($host, FILTER_VALIDATE_DOMAIN)) {
            $this->ip = gethostbyname($host);
        }
        else {
            $this->ip = $this->host;
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->host;
    }
}
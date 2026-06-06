<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Scrapers;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\ScraperInterface;

abstract class JsonScraper extends ProxyScraper implements ScraperInterface
{
    protected string $hostProperty = 'ip';
    protected string $portProperty = 'port';
    protected string $protocolProperty = 'protocol';

    /**
     * @return Generator<int, Proxy>
     * @throws InvalidArgumentException
     * @throws ScraperException
     */
    public function get(): Generator
    {
        try {
            $response = $this->httpClient->request('GET', $this->getUrl())->getContent();
        } catch (\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        yield $this->extractProxy($response);
    }

    /**
     * @param string $response
     * @return Proxy
     * @throws InvalidArgumentException
     * @throws ScraperException
     */
    private function extractProxy(string $response): Proxy
    {
        $json = json_decode($response, true);

        $host = is_array($json) ? ($json[$this->hostProperty] ?? null) : null;
        $port = is_array($json) ? ($json[$this->portProperty] ?? null) : null;
        $protocol = is_array($json) ? ($json[$this->protocolProperty] ?? null) : null;

        if (!is_scalar($host) || !is_scalar($port) || !is_scalar($protocol)) {
            throw new ScraperException('Failed to extract, response (' . $response . ')');
        }

        return $this->makeProxy((string) $host, (string) $port, (string) $protocol);
    }
}
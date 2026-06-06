<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Scrapers;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\ScraperInterface;

abstract class JsonListScraper extends ProxyScraper implements ScraperInterface
{
    protected ?string $listPath = null;
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

        $json = json_decode($response, true);
        $list = $this->listPath
            ? (is_array($json) ? ($json[$this->listPath] ?? null) : null)
            : $json;

        if (!is_array($list)) {
            throw new ScraperException('Failed to extract proxy list, response (' . $response . ')');
        }

        foreach ($list as $item) {
            if (!is_array($item)) {
                continue;
            }
            yield $this->extractProxy($item);
        }
    }

    /**
     * @param array<array-key, mixed> $item
     * @return Proxy
     * @throws InvalidArgumentException
     * @throws ScraperException
     */
    private function extractProxy(array $item): Proxy
    {
        $host = $item[$this->hostProperty] ?? null;
        $port = $item[$this->portProperty] ?? null;
        $protocol = $item[$this->protocolProperty] ?? null;

        if (!is_scalar($host) || !is_scalar($port) || !is_scalar($protocol)) {
            throw new ScraperException('Failed to extract, response (' . json_encode($item) . ')');
        }

        return $this->makeProxy((string) $host, (string) $port, (string) $protocol);
    }
}
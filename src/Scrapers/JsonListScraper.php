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
            $proxy = $this->extractProxy($item);
            if ($proxy !== null) {
                yield $proxy;
            }
        }
    }

    /**
     * Build a Proxy from one list item, or null when the item is malformed.
     * A single bad entry is skipped rather than aborting the whole source,
     * consistent with the text/table scrapers and GeonodeProxyList.
     *
     * @param array<array-key, mixed> $item
     */
    private function extractProxy(array $item): ?Proxy
    {
        $host = $item[$this->hostProperty] ?? null;
        $port = $item[$this->portProperty] ?? null;
        $protocol = $item[$this->protocolProperty] ?? null;

        if (!is_scalar($host) || !is_scalar($port) || !is_scalar($protocol)) {
            return null;
        }

        try {
            return $this->makeProxy((string) $host, (string) $port, (string) $protocol);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }
}

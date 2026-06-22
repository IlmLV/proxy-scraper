<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Scrapers;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\ScraperInterface;

/**
 * Base for sources whose endpoint returns a JSON array of proxy objects.
 *
 * Config a source may override:
 * - $listPath             key under which the array lives in the response; null
 *                         when the response body is itself the array.
 * - JSON field names      via {@see JsonFieldMapping} ($hostProperty etc.).
 */
abstract class JsonListScraper extends ProxyScraper implements ScraperInterface
{
    use JsonFieldMapping;

    protected ?string $listPath = null;

    /**
     * @return Generator<int, Proxy>
     * @throws ScraperException
     */
    public function get(): Generator
    {
        $response = $this->fetch();

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

<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Scrapers;

use Generator;
use IlmLV\ProxyScraper\Arr;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;

/**
 * Base for sources whose endpoint returns a JSON array of proxy objects.
 *
 * Config a source may override:
 * - $listPath             key under which the array lives in the response; null
 *                         when the response body is itself the array.
 * - JSON field names      via {@see JsonFieldMapping} ($hostProperty etc.). A
 *                         {@see ProxyScraper::$protocols} map key overrides the
 *                         per-item protocol field for that fetched list.
 */
abstract class JsonListScraper extends ProxyScraper
{
    use JsonFieldMapping;
    use MultiProtocolFetch;

    protected ?string $listPath = null;

    /**
     * @return Generator<int, Proxy>
     * @throws ScraperException
     */
    protected function parse(string $body, ?string $protocol): Generator
    {
        $json = json_decode($body, true);
        $list = $this->listPath !== null
            ? Arr::get($json, $this->listPath)
            : $json;

        if (!is_array($list)) {
            throw new ScraperException('Failed to extract proxy list, response (' . $body . ')');
        }

        foreach ($list as $item) {
            if (!is_array($item)) {
                continue;
            }
            $proxy = $this->extractProxy($item, $protocol);
            if ($proxy !== null) {
                yield $proxy;
            }
        }
    }

    /**
     * Build a Proxy from one list item, or null when the item is malformed.
     * A single bad entry is skipped rather than aborting the whole source,
     * consistent with the text/table scrapers and Geonode. A forced $protocol
     * (a $protocols map key) overrides the item's protocol field.
     *
     * @param array<array-key, mixed> $item
     */
    private function extractProxy(array $item, ?string $protocol): ?Proxy
    {
        $host = $item[$this->hostProperty] ?? null;
        $port = $item[$this->portProperty] ?? null;
        $protocol ??= $item[$this->protocolProperty] ?? null;

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

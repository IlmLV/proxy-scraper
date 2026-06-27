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
 * Base for a source whose endpoint returns a single proxy as one JSON object
 * (e.g. {"ip": ..., "port": ..., "protocol": ...}). For an endpoint that returns
 * an array of proxies use {@see JsonListScraper} instead.
 *
 * No bundled source currently extends this — it is a supported extension point.
 * Because exactly one proxy is expected per response, a malformed payload throws
 * rather than being skipped (there is nothing else in the response to fall back
 * to). With a {@see ProxyScraper::$protocols} map it yields one proxy per URL, the
 * map key forcing that proxy's protocol.
 */
abstract class JsonScraper extends ProxyScraper
{
    use JsonFieldMapping;
    use MultiProtocolFetch;

    /**
     * @return Generator<int, Proxy>
     * @throws InvalidArgumentException
     * @throws ScraperException
     */
    protected function parse(string $body, ?string $protocol): Generator
    {
        yield $this->extractProxy($body, $protocol);
    }

    /**
     * @throws InvalidArgumentException
     * @throws ScraperException
     */
    private function extractProxy(string $response, ?string $protocol): Proxy
    {
        $json = json_decode($response, true);

        $host = Arr::get($json, $this->hostProperty);
        $port = Arr::get($json, $this->portProperty);
        $protocol ??= Arr::get($json, $this->protocolProperty);

        if (!is_scalar($host) || !is_scalar($port) || !is_scalar($protocol)) {
            throw new ScraperException('Failed to extract, response (' . $response . ')');
        }

        return $this->makeProxy((string) $host, (string) $port, (string) $protocol);
    }
}

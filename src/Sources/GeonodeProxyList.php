<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use Generator;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\ScraperInterface;

/**
 * Geonode exposes its own checked proxy database as JSON. Each entry lists one
 * or more protocols in a "protocols" array (e.g. ["http","https"]), so it can't
 * use JsonListScraper (which expects a scalar protocol) — we yield one Proxy per
 * listed protocol here. A single page of 500 keeps us well under the public
 * rate limit (~100 req/h) without pagination.
 */
final class GeonodeProxyList extends ProxyScraper implements ScraperInterface
{
    protected string $url = 'https://proxylist.geonode.com/api/proxy-list?limit=500&page=1&sort_by=lastChecked&sort_type=desc';

    public const SCHEDULE = '0 * * * *';

    /**
     * @return Generator<int, \IlmLV\ProxyScraper\Entities\Proxy>
     * @throws InvalidArgumentException
     * @throws ScraperException
     */
    public function get(): Generator
    {
        $response = $this->fetch();

        $json = json_decode($response, true);
        $list = is_array($json) ? ($json['data'] ?? null) : null;

        if (!is_array($list)) {
            throw new ScraperException('Failed to extract proxy list, response (' . $response . ')');
        }

        foreach ($list as $item) {
            if (!is_array($item)) {
                continue;
            }

            $ip = $item['ip'] ?? null;
            $port = $item['port'] ?? null;
            $protocols = $item['protocols'] ?? null;

            if (!is_scalar($ip) || !is_scalar($port) || !is_array($protocols)) {
                continue;
            }

            foreach ($protocols as $protocol) {
                if (!is_scalar($protocol)) {
                    continue;
                }
                try {
                    yield $this->makeProxy((string) $ip, (string) $port, (string) $protocol);
                } catch (InvalidArgumentException $e) {
                    continue;
                }
            }
        }
    }
}

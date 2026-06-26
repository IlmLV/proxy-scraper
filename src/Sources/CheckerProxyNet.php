<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use Generator;
use IlmLV\ProxyScraper\Arr;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;

final class CheckerProxyNet extends ProxyScraper
{
    protected string $url = 'https://api.checkerproxy.net/v1/landing/archive';
    public const SCHEDULE = '0 0 * * *';

    /**
     * The archive API no longer exposes a proxy type/protocol, just an
     * "ip:port" list, so we fall back to this protocol for every entry.
     */
    protected string $protocol = 'http';

    /**
     * @throws InvalidArgumentException
     * @throws ScraperException
     */
    public function get(): Generator
    {
        $date = $this->latestArchiveDate();

        $response = $this->fetchUrl($this->url . '/' . $date);

        $json = json_decode($response, true);
        $proxyList = Arr::get($json, 'data.proxyList');

        if (!is_array($proxyList)) {
            throw new ScraperException('Failed to extract proxy list, response (' . $response . ')');
        }

        foreach ($proxyList as $address) {
            if (!is_string($address)) {
                continue;
            }
            try {
                yield Proxy::fromString($this->protocol . '://' . $address);
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }
    }

    /**
     * The archive is published with a few days of delay, so we resolve the most
     * recent available date from the archive index before fetching its proxies.
     *
     * @throws ScraperException
     */
    private function latestArchiveDate(): string
    {
        $response = $this->fetchUrl($this->url);

        $json = json_decode($response, true);
        $date = Arr::get($json, 'data.items.0.date');

        if (!is_string($date)) {
            throw new ScraperException('Failed to resolve latest archive date, response (' . $response . ')');
        }

        return $date;
    }
}

<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\ScraperInterface;

final class CheckerProxyNet extends ProxyScraper implements ScraperInterface
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

        try {
            $response = $this->httpClient->request('GET', $this->url . '/' . $date)->getContent();
        } catch (\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        $json = json_decode($response, true);
        $data = is_array($json) ? ($json['data'] ?? null) : null;
        $proxyList = is_array($data) ? ($data['proxyList'] ?? null) : null;

        if (!is_array($proxyList)) {
            throw new ScraperException('Failed to extract proxy list, response (' . $response . ')');
        }

        foreach ($proxyList as $address) {
            if (!is_string($address)) {
                continue;
            }
            try {
                yield new Proxy($this->protocol . '://' . $address);
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
        try {
            $response = $this->httpClient->request('GET', $this->url)->getContent();
        } catch (\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        $json = json_decode($response, true);
        $data = is_array($json) ? ($json['data'] ?? null) : null;
        $items = is_array($data) ? ($data['items'] ?? null) : null;
        $first = is_array($items) ? ($items[0] ?? null) : null;
        $date = is_array($first) ? ($first['date'] ?? null) : null;

        if (!is_string($date)) {
            throw new ScraperException('Failed to resolve latest archive date, response (' . $response . ')');
        }

        return $date;
    }
}

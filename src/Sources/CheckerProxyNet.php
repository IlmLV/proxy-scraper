<?php

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
    const SCHEDULE = '0 0 * * *';

    /**
     * The archive API no longer exposes a proxy type/protocol, just an
     * "ip:port" list, so we fall back to this protocol for every entry.
     */
    protected string $protocol = 'http';

    /**
     * @return Generator
     * @throws InvalidArgumentException
     * @throws ScraperException
     */
    public function get(): Generator
    {
        $date = $this->latestArchiveDate();

        try {
            $response = $this->httpClient->request('GET', $this->url . '/' . $date)->getContent();
        } catch (\Exception|\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        $json = json_decode($response);

        if (!isset($json->data->proxyList) || !is_array($json->data->proxyList)) {
            throw new ScraperException('Failed to extract proxy list, response (' . $response . ')');
        }

        foreach ($json->data->proxyList as $address) {
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
     * @return string
     * @throws ScraperException
     */
    private function latestArchiveDate(): string
    {
        try {
            $response = $this->httpClient->request('GET', $this->url)->getContent();
        } catch (\Exception|\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        $json = json_decode($response);

        if (!isset($json->data->items[0]->date)) {
            throw new ScraperException('Failed to resolve latest archive date, response (' . $response . ')');
        }

        return $json->data->items[0]->date;
    }
}

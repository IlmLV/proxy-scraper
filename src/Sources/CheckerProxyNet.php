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
    protected string $url = 'https://checkerproxy.net/api/archive/%s';
    const SCHEDULE = '0 0 * * *';

    const TYPE_PROTOCOL_MAPPING = [
        1 => 'http',
        2 => 'https',
        3 => 'socks4',
        4 => 'socks5'
    ];

    /**
     * @return Generator
     * @throws InvalidArgumentException
     * @throws ScraperException
     */
    public function get(): Generator
    {
        try {
            $response = $this->httpClient->request('GET', $this->getUrl(date('Y-m-d')))->getContent();
        } catch (\Exception|\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        $json = json_decode($response);

        foreach ($json as $item) {
            yield $this->extractProxy($item);
        }
    }

    /**
     * @param object $json
     * @return Proxy
     * @throws InvalidArgumentException
     * @throws ScraperException
     */
    private function extractProxy(object $json): Proxy
    {
        if (property_exists($json, 'addr')
            && property_exists($json, 'type')
            && array_key_exists($json->type, self::TYPE_PROTOCOL_MAPPING)
        ) {
            return new Proxy(
                self::TYPE_PROTOCOL_MAPPING[$json->type] . '://' .  $json->addr
            );
        }
        else {
            throw new ScraperException('Failed to extract, response (' . json_encode($json) . ')');
        }
    }
}
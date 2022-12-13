<?php

namespace IlmLV\ProxyScraper\Scrapers;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\ScraperInterface;

abstract class JsonListScrapper extends ProxyScraper implements ScraperInterface
{
    protected ?string $listPath = null;
    protected string $hostProperty = 'ip';
    protected string $portProperty = 'port';
    protected string $protocolProperty = 'protocol';

    /**
     * @return Generator
     * @throws InvalidArgumentException
     * @throws ScraperException
     */
    public function get(): Generator
    {
        try {
            $response = $this->httpClient->request('GET', $this->getUrl())->getContent();
        } catch (\Exception|\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        $json = json_decode($response);
        $list = $this->listPath ? $json->{$this->listPath} : $json;

        foreach ($list as $item) {
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
        if (property_exists($json, $this->hostProperty)
            && property_exists($json, $this->portProperty)
            && property_exists($json, $this->protocolProperty)
        ) {
            return $this->makeProxy(
                $json->{$this->hostProperty},
                $json->{$this->portProperty},
                $json->{$this->protocolProperty}
            );
        }
        else {
            throw new ScraperException('Failed to extract, response (' . json_encode($json) . ')');
        }
    }
}
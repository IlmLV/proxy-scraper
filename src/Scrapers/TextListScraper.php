<?php

namespace IlmLV\ProxyScraper\Scrapers;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\ScraperInterface;

abstract class TextListScrapper extends ProxyScraper implements ScraperInterface
{
    protected string $protocol = 'http';

    /**
     * @return Generator
     * @throws ScraperException
     */
    public function get(): Generator
    {
        try {
            $text = $this->httpClient->request('GET', $this->url)->getContent();
        } catch (\Exception|\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        foreach (explode("\n", $text) as $line) {
            try {
                $proxy = (new Proxy($this->protocol . '://' . $line));
            } catch (InvalidArgumentException $e) {
                continue;
            }

            yield $proxy;
        }
    }
}
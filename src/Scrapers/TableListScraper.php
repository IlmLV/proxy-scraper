<?php

namespace IlmLV\ProxyScraper\Scrapers;

use Generator;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\ScraperInterface;
use Symfony\Component\DomCrawler\Crawler as Dom;

abstract class TableListScraper extends ProxyScraper implements ScraperInterface
{
    protected ?string $protocol = null;
    protected string $rowPath = 'table tbody tr';
    protected int $colAddress = 0;
    protected int $colPort = 1;
    protected int $colProtocol = 2;

    /**
     * @return Generator
     * @throws ScraperException
     */
    public function get(): Generator
    {
        try {
            $html = $this->httpClient->request('GET', $this->url)->getContent();
            $rows = (new Dom($html))->filter($this->rowPath);
        } catch (\Exception|\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        foreach ($rows as $row) {
            $rowDom = new Dom($row);
            $address = $rowDom->filter('td')->eq($this->colAddress)->text();
            $port = $rowDom->filter('td')->eq($this->colPort)->text();
            $protocol = $this->protocol ?: strtolower($rowDom->filter('td')->eq($this->colProtocol)->text());

            try {
                yield $this->makeProxy($address, $port, $protocol);
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }
    }
}
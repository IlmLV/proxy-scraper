<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Scrapers;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
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
     * @return Generator<int, Proxy>
     * @throws ScraperException
     */
    public function get(): Generator
    {
        try {
            $html = $this->httpClient->request('GET', $this->url)->getContent();
            $rows = (new Dom($html))->filter($this->rowPath);
        } catch (\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        foreach ($rows as $row) {
            $cells = (new Dom($row))->filter('td');

            // Skip header/spacer/malformed rows that lack the columns we read;
            // Crawler::text() throws on an empty node, which would abort the scrape.
            $needed = $this->protocol === null
                ? max($this->colAddress, $this->colPort, $this->colProtocol)
                : max($this->colAddress, $this->colPort);
            if ($cells->count() <= $needed) {
                continue;
            }

            $address = $cells->eq($this->colAddress)->text();
            $port = $cells->eq($this->colPort)->text();
            $protocol = $this->protocol ?: strtolower($cells->eq($this->colProtocol)->text());

            try {
                yield $this->makeProxy($address, $port, $protocol);
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }
    }
}
<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Scrapers;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use Symfony\Component\DomCrawler\Crawler as Dom;

/**
 * Base for sources that publish proxies in an HTML table.
 *
 * Config a source may override:
 * - $rowPath              CSS selector for the proxy rows (default "table tbody tr").
 * - $colAddress/$colPort  zero-based column indices for host and port.
 * - $protocol             fixed protocol for every row; when null it is read from
 *                         column $colProtocol instead. A {@see ProxyScraper::$protocols}
 *                         map key overrides this per fetched table.
 */
abstract class TableListScraper extends ProxyScraper
{
    use MultiProtocolFetch;

    protected ?string $protocol = null;
    protected string $rowPath = 'table tbody tr';
    protected int $colAddress = 0;
    protected int $colPort = 1;
    protected int $colProtocol = 2;

    /**
     * @return Generator<int, Proxy>
     * @throws ScraperException
     */
    protected function parse(string $body, ?string $protocol): Generator
    {
        // The forced $protocol (a $protocols map key) wins; otherwise $this->protocol;
        // a null result means the protocol is read from column $colProtocol per row.
        $fixed = $protocol ?? $this->protocol;

        try {
            $rows = (new Dom($body))->filter($this->rowPath);
        } catch (\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        foreach ($rows as $row) {
            $cells = (new Dom($row))->filter('td');

            // Skip header/spacer/malformed rows that lack the columns we read;
            // Crawler::text() throws on an empty node, which would abort the scrape.
            $needed = $fixed === null
                ? max($this->colAddress, $this->colPort, $this->colProtocol)
                : max($this->colAddress, $this->colPort);
            if ($cells->count() <= $needed) {
                continue;
            }

            $address = $cells->eq($this->colAddress)->text();
            $port = $cells->eq($this->colPort)->text();
            $rowProtocol = $fixed ?? strtolower($cells->eq($this->colProtocol)->text());

            try {
                yield $this->makeProxy($address, $port, $rowProtocol);
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }
    }
}

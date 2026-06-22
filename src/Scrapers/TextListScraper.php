<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Scrapers;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\ScraperInterface;

abstract class TextListScraper extends ProxyScraper implements ScraperInterface
{
    /**
     * Protocol to prepend to each bare "ip:port" line. When null, the line is
     * expected to already carry its scheme (e.g. "socks5://1.2.3.4:1080") and the
     * Proxy constructor reads it per row.
     */
    protected ?string $protocol = null;

    /**
     * @return Generator<int, Proxy>
     * @throws ScraperException
     */
    public function get(): Generator
    {
        try {
            $text = $this->httpClient->request('GET', $this->url)->getContent();
        } catch (\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            try {
                $proxy = new Proxy($this->protocol === null ? $line : $this->protocol . '://' . $line);
            } catch (InvalidArgumentException $e) {
                continue;
            }

            yield $proxy;
        }
    }
}

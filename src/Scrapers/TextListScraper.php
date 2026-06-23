<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Scrapers;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;

abstract class TextListScraper extends ProxyScraper
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
        $text = $this->fetch();

        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            try {
                $proxy = Proxy::fromString($this->protocol === null ? $line : $this->protocol . '://' . $line);
            } catch (InvalidArgumentException $e) {
                continue;
            }

            yield $proxy;
        }
    }
}

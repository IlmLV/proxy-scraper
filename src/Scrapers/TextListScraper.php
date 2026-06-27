<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Scrapers;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\ProxyScraper;

abstract class TextListScraper extends ProxyScraper
{
    use MultiProtocolFetch;

    /**
     * Protocol to prepend to each bare "ip:port" line. When null, the line is
     * expected to already carry its scheme (e.g. "socks5://1.2.3.4:1080") and the
     * Proxy constructor reads it per row. A {@see ProxyScraper::$protocols} map key
     * overrides this per fetched list.
     */
    protected ?string $protocol = null;

    /**
     * Parse a fetched list body into proxies, prepending the effective protocol to
     * each bare "ip:port" line (or reading the scheme per line when it is null).
     * The forced $protocol (a $protocols map key) wins; otherwise $this->protocol
     * is used. Lines that fail to parse are skipped.
     *
     * @return Generator<int, Proxy>
     */
    protected function parse(string $body, ?string $protocol): Generator
    {
        $protocol ??= $this->protocol;

        foreach (explode("\n", $body) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            try {
                $proxy = Proxy::fromString($protocol === null ? $line : $protocol . '://' . $line);
            } catch (InvalidArgumentException $e) {
                continue;
            }

            yield $proxy;
        }
    }
}

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
     * Protocol => URL map for providers that publish one list per protocol on the
     * same site/schedule. When non-empty it takes precedence over $url/$protocol:
     * get() fetches each URL and prepends its key as the scheme. A single dead
     * endpoint is skipped rather than aborting the whole source, matching the
     * "a failing source never aborts the batch" guarantee. Leave empty (the
     * default) for the single-URL case.
     *
     * @var array<string, string>
     */
    protected array $protocols = [];

    /**
     * @return Generator<int, Proxy>
     * @throws ScraperException
     */
    public function get(): Generator
    {
        if ($this->protocols === []) {
            yield from $this->parse($this->fetch(), $this->protocol);

            return;
        }

        foreach ($this->protocols as $protocol => $url) {
            try {
                $text = $this->fetchUrl($this->appendOptions($url));
            } catch (ScraperException $e) {
                continue;
            }

            yield from $this->parse($text, $protocol);
        }
    }

    /**
     * Parse a fetched list body into proxies, prepending $protocol to each bare
     * "ip:port" line (or reading the scheme per line when $protocol is null).
     * Lines that fail to parse are skipped.
     *
     * @return Generator<int, Proxy>
     */
    private function parse(string $text, ?string $protocol): Generator
    {
        foreach (explode("\n", $text) as $line) {
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

<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Scrapers;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\ScraperException;

/**
 * Shared get() for the format scrapers (text / table / JSON). It honours the
 * base {@see \IlmLV\ProxyScraper\ProxyScraper::$protocols} map:
 *
 * - When $protocols is empty (the default) the single $url is fetched and its
 *   body parsed with no forced protocol — each scraper falls back to its own
 *   $protocol / per-row / per-field resolution.
 * - When $protocols is a `protocol => URL` map each URL is fetched (with the
 *   configured options applied) and its body parsed with that protocol forced.
 *   A dead endpoint is skipped rather than aborting the source, honouring the
 *   "a failing source never aborts the batch" guarantee.
 *
 * Each scraper supplies {@see parse()} to turn one fetched body into proxies.
 */
trait MultiProtocolFetch
{
    /**
     * @return Generator<int, Proxy>
     * @throws ScraperException
     */
    public function get(): Generator
    {
        if ($this->protocols === []) {
            yield from $this->parse($this->fetch(), null);

            return;
        }

        foreach ($this->protocols as $protocol => $url) {
            try {
                yield from $this->parse($this->fetchUrl($this->appendOptions($url)), (string) $protocol);
            } catch (ScraperException $e) {
                continue;
            }
        }
    }

    /**
     * Turn one fetched body into proxies. $protocol is the protocol forced for
     * this body (a $protocols map key), or null to use the scraper's own protocol
     * resolution.
     *
     * @return Generator<int, Proxy>
     */
    abstract protected function parse(string $body, ?string $protocol): Generator;
}

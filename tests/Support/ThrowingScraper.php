<?php

namespace IlmLV\ProxyScraper\Tests\Support;

use Generator;
use IlmLV\ProxyScraper\ProxyScraper;

/**
 * A source whose get() throws a non-ProxyScraperException, used to verify
 * LoadProxies captures any throwable instead of aborting the batch.
 */
class ThrowingScraper extends ProxyScraper
{
    protected string $url = 'https://throwing.test/';

    public function get(): Generator
    {
        throw new \RuntimeException('boom');
        yield; // unreachable; the yield only makes this a generator so the throw fires on iteration
    }
}

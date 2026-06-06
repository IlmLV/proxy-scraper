<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;

interface ScraperInterface
{
    /**
     * @return Generator<int, Proxy>
     */
    public function get(): Generator;
}
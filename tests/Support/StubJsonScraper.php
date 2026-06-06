<?php

namespace IlmLV\ProxyScraper\Tests\Support;

use IlmLV\ProxyScraper\Scrapers\JsonScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

/**
 * Minimal single-object JSON source (no live source uses JsonScrapper
 * after GimmeProxyCom was removed) so the base class stays covered.
 */
class StubJsonScrapper extends JsonScrapper implements ScraperInterface
{
    protected string $url = 'https://json.test/proxy';
}

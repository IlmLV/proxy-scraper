<?php

namespace IlmLV\ProxyScraper\Tests\Support;

use IlmLV\ProxyScraper\Scrapers\JsonScraper;
use IlmLV\ProxyScraper\ScraperInterface;

/**
 * Minimal single-object JSON source (no live source uses JsonScraper
 * after GimmeProxyCom was removed) so the base class stays covered.
 */
class StubJsonScraper extends JsonScraper implements ScraperInterface
{
    protected string $url = 'https://json.test/proxy';
}

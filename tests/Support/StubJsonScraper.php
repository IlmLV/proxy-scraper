<?php

namespace IlmLV\ProxyScraper\Tests\Support;

use IlmLV\ProxyScraper\Scrapers\JsonScraper;

/**
 * Minimal single-object JSON source (no live source uses JsonScraper
 * after GimmeProxyCom was removed) so the base class stays covered.
 */
class StubJsonScraper extends JsonScraper
{
    protected string $url = 'https://json.test/proxy';
}

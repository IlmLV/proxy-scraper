<?php

namespace IlmLV\ProxyScraper\Tests\Support;

use IlmLV\ProxyScraper\Scrapers\JsonListScraper;
use IlmLV\ProxyScraper\ScraperInterface;

/**
 * JSON list source with the root array as the list (listPath = null),
 * complementing PubProxyCom which uses a nested listPath.
 */
class StubJsonListScraper extends JsonListScraper implements ScraperInterface
{
    protected string $url = 'https://json.test/list';
}

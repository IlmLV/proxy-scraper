<?php

namespace IlmLV\ProxyScraper\Tests\Support;

use IlmLV\ProxyScraper\Scrapers\JsonListScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

/**
 * JSON list source with the root array as the list (listPath = null),
 * complementing PubProxyCom which uses a nested listPath.
 */
class StubJsonListScrapper extends JsonListScrapper implements ScraperInterface
{
    protected string $url = 'https://json.test/list';
}

<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\JsonScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

final class GimmeProxyCom extends JsonScrapper implements ScraperInterface
{
    protected string $url = 'https://gimmeproxy.com/api/getProxy';
}

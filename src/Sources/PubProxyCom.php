<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\JsonListScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

final class PubProxyCom extends JsonListScrapper implements ScraperInterface
{
    protected string $url = 'http://pubproxy.com/api/proxy?limit=5&format=json';
    protected ?string $listPath = 'data';
    protected string $protocolProperty = 'type';

    const SCHEDULE = '0,30 * * * *';
}
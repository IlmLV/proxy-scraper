<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\JsonListScraper;
use IlmLV\ProxyScraper\ScraperInterface;

final class PubProxyCom extends JsonListScraper implements ScraperInterface
{
    protected string $url = 'http://pubproxy.com/api/proxy?limit=5&format=json';
    protected ?string $listPath = 'data';
    protected string $protocolProperty = 'type';

    const SCHEDULE = '0,30 * * * *';
}
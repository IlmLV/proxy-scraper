<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;
use IlmLV\ProxyScraper\ScraperInterface;

final class ClarketmProxyList extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/clarketm/proxy-list/master/proxy-list-raw.txt';

    const SCHEDULE = '0 0 * * *';
}
<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;
use IlmLV\ProxyScraper\ScraperInterface;

final class MonosansProxyListHttp extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/monosans/proxy-list/main/proxies/http.txt';

    const SCHEDULE = '0 * * * *';
}

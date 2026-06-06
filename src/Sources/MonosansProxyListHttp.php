<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

final class MonosansProxyListHttp extends TextListScrapper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/monosans/proxy-list/main/proxies/http.txt';

    const SCHEDULE = '0 * * * *';
}

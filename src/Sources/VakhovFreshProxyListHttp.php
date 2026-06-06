<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

final class VakhovFreshProxyListHttp extends TextListScrapper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/vakhov/fresh-proxy-list/master/http.txt';

    const SCHEDULE = '0 * * * *';
}

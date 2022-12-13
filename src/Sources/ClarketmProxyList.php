<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

final class ClarketmProxyList extends TextListScrapper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/clarketm/proxy-list/master/proxy-list-raw.txt';

    const SCHEDULE = '0 0 * * *';
}
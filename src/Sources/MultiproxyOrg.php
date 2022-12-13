<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

final class MultiproxyOrg extends TextListScrapper implements ScraperInterface
{
    protected string $url = 'https://multiproxy.org/txt_all/proxy.txt';

    const SCHEDULE = '0 0 * * *';
}
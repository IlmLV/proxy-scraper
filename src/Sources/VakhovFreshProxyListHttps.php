<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

final class VakhovFreshProxyListHttps extends TextListScrapper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/vakhov/fresh-proxy-list/master/https.txt';

    protected string $protocol = 'https';

    const SCHEDULE = '0 * * * *';
}

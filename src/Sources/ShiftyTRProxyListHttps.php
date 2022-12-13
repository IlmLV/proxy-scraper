<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

final class ShiftyTRProxyListHttps extends TextListScrapper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/ShiftyTR/Proxy-List/master/https.txt';

    protected string $protocol = 'https';

    const SCHEDULE = '0 * * * *';
}
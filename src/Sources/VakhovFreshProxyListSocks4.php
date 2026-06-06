<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

final class VakhovFreshProxyListSocks4 extends TextListScrapper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/vakhov/fresh-proxy-list/master/socks4.txt';

    protected string $protocol = 'socks4';

    const SCHEDULE = '0 * * * *';
}

<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

final class RoosterkidOpenProxyListSocks4 extends TextListScrapper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/roosterkid/openproxylist/main/SOCKS4_RAW.txt';

    protected string $protocol = 'socks4';

    const SCHEDULE = '0 * * * *';
}

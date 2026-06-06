<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

final class RoosterkidOpenProxyListSocks5 extends TextListScrapper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/roosterkid/openproxylist/main/SOCKS5_RAW.txt';

    protected string $protocol = 'socks5';

    const SCHEDULE = '0 * * * *';
}

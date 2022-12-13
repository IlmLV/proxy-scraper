<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

final class TheSpeedXProxyListSocks5 extends TextListScrapper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/socks5.txt';

    protected string $protocol = 'socks5';

    const SCHEDULE = '0 * * * *';
}
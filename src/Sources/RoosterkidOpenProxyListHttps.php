<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScrapper;
use IlmLV\ProxyScraper\ScraperInterface;

final class RoosterkidOpenProxyListHttps extends TextListScrapper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/roosterkid/openproxylist/main/HTTPS_RAW.txt';

    protected string $protocol = 'https';

    const SCHEDULE = '0 * * * *';
}

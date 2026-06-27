<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class BesJs extends TextListScraper
{
    protected string $url = 'https://raw.githubusercontent.com/Bes-js/public-proxy-list/main/proxies.txt';

    // $protocol stays null: each line carries its own scheme (http://, socks4://, socks5://).

    public const SCHEDULE = '0 * * * *';
}

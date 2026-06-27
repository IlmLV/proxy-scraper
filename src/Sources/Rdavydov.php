<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class Rdavydov extends TextListScraper
{
    protected array $protocols = [
        'http' => 'https://raw.githubusercontent.com/rdavydov/proxy-list/main/proxies_anonymous/http.txt',
        'socks4' => 'https://raw.githubusercontent.com/rdavydov/proxy-list/main/proxies_anonymous/socks4.txt',
        'socks5' => 'https://raw.githubusercontent.com/rdavydov/proxy-list/main/proxies_anonymous/socks5.txt',
    ];

    public const SCHEDULE = '0 * * * *';
}

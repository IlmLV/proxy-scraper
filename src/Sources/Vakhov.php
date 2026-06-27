<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class Vakhov extends TextListScraper
{
    protected array $protocols = [
        'http' => 'https://raw.githubusercontent.com/vakhov/fresh-proxy-list/master/http.txt',
        'https' => 'https://raw.githubusercontent.com/vakhov/fresh-proxy-list/master/https.txt',
        'socks4' => 'https://raw.githubusercontent.com/vakhov/fresh-proxy-list/master/socks4.txt',
        'socks5' => 'https://raw.githubusercontent.com/vakhov/fresh-proxy-list/master/socks5.txt',
    ];

    public const SCHEDULE = '0 * * * *';
}

<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class SevenworksDev extends TextListScraper
{
    protected array $protocols = [
        'http' => 'https://raw.githubusercontent.com/SevenworksDev/proxy-list/main/proxies/http.txt',
        'https' => 'https://raw.githubusercontent.com/SevenworksDev/proxy-list/main/proxies/https.txt',
        'socks4' => 'https://raw.githubusercontent.com/SevenworksDev/proxy-list/main/proxies/socks4.txt',
        'socks5' => 'https://raw.githubusercontent.com/SevenworksDev/proxy-list/main/proxies/socks5.txt',
    ];

    public const SCHEDULE = '0 * * * *';
}

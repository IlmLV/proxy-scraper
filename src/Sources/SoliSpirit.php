<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class SoliSpirit extends TextListScraper
{
    protected array $protocols = [
        'http' => 'https://raw.githubusercontent.com/SoliSpirit/proxy-list/main/http.txt',
        'https' => 'https://raw.githubusercontent.com/SoliSpirit/proxy-list/main/https.txt',
        'socks4' => 'https://raw.githubusercontent.com/SoliSpirit/proxy-list/main/socks4.txt',
        'socks5' => 'https://raw.githubusercontent.com/SoliSpirit/proxy-list/main/socks5.txt',
    ];

    public const SCHEDULE = '0 * * * *';
}

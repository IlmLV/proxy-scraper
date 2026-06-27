<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class Aliilapro extends TextListScraper
{
    protected array $protocols = [
        'http' => 'https://raw.githubusercontent.com/ALIILAPRO/Proxy/main/http.txt',
        'socks4' => 'https://raw.githubusercontent.com/ALIILAPRO/Proxy/main/socks4.txt',
        'socks5' => 'https://raw.githubusercontent.com/ALIILAPRO/Proxy/main/socks5.txt',
    ];

    public const SCHEDULE = '0 * * * *';
}

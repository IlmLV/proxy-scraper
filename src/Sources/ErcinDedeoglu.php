<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class ErcinDedeoglu extends TextListScraper
{
    protected array $protocols = [
        'http' => 'https://raw.githubusercontent.com/ErcinDedeoglu/proxies/main/proxies/http.txt',
        'socks4' => 'https://raw.githubusercontent.com/ErcinDedeoglu/proxies/main/proxies/socks4.txt',
        'socks5' => 'https://raw.githubusercontent.com/ErcinDedeoglu/proxies/main/proxies/socks5.txt',
    ];

    public const SCHEDULE = '0 * * * *';
}

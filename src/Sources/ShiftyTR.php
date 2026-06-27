<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class ShiftyTR extends TextListScraper
{
    protected array $protocols = [
        'http' => 'https://raw.githubusercontent.com/ShiftyTR/Proxy-List/master/http.txt',
        'https' => 'https://raw.githubusercontent.com/ShiftyTR/Proxy-List/master/https.txt',
        'socks4' => 'https://raw.githubusercontent.com/ShiftyTR/Proxy-List/master/socks4.txt',
        'socks5' => 'https://raw.githubusercontent.com/ShiftyTR/Proxy-List/master/socks5.txt',
    ];

    public const SCHEDULE = '0 * * * *';
}

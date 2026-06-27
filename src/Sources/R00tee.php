<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class R00tee extends TextListScraper
{
    // The repo ships capitalized filenames and has no Http.txt (https/socks only).
    protected array $protocols = [
        'https' => 'https://raw.githubusercontent.com/r00tee/Proxy-List/main/Https.txt',
        'socks4' => 'https://raw.githubusercontent.com/r00tee/Proxy-List/main/Socks4.txt',
        'socks5' => 'https://raw.githubusercontent.com/r00tee/Proxy-List/main/Socks5.txt',
    ];

    public const SCHEDULE = '0 * * * *';
}

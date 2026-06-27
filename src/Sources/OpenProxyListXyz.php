<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class OpenProxyListXyz extends TextListScraper
{
    protected array $protocols = [
        'http' => 'https://api.openproxylist.xyz/http.txt',
        'socks4' => 'https://api.openproxylist.xyz/socks4.txt',
        'socks5' => 'https://api.openproxylist.xyz/socks5.txt',
    ];

    public const SCHEDULE = '0 * * * *';
}

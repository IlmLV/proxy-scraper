<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class VPSLabCloud extends TextListScraper
{
    // Files carry "#" comment headers and CRLF line endings; TextListScraper::parse()
    // trims the CR and skips lines that fail Proxy::fromString(), so both are dropped.
    protected array $protocols = [
        'http' => 'https://raw.githubusercontent.com/VPSLabCloud/VPSLab-Free-Proxy-List/main/http_all.txt',
        'socks4' => 'https://raw.githubusercontent.com/VPSLabCloud/VPSLab-Free-Proxy-List/main/socks4_all.txt',
        'socks5' => 'https://raw.githubusercontent.com/VPSLabCloud/VPSLab-Free-Proxy-List/main/socks5_all.txt',
    ];

    public const SCHEDULE = '0 * * * *';
}

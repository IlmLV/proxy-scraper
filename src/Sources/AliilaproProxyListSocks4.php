<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class AliilaproProxyListSocks4 extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/ALIILAPRO/Proxy/main/socks4.txt';

    protected ?string $protocol = 'socks4';

    public const SCHEDULE = '0 * * * *';
}

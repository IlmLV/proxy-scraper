<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;
use IlmLV\ProxyScraper\ScraperInterface;

final class AliilaproProxyListSocks4 extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/ALIILAPRO/Proxy/main/socks4.txt';

    protected ?string $protocol = 'socks4';

    const SCHEDULE = '0 * * * *';
}

<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class ShiftyTRProxyListSocks5 extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/ShiftyTR/Proxy-List/master/socks5.txt';

    protected ?string $protocol = 'socks5';

    public const SCHEDULE = '0 * * * *';
}

<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;
use IlmLV\ProxyScraper\ScraperInterface;

final class HookzofSocks5List extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/hookzof/socks5_list/master/proxy.txt';

    protected ?string $protocol = 'socks5';

    const SCHEDULE = '0 * * * *';
}

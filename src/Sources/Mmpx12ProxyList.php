<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class Mmpx12ProxyList extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/mmpx12/proxy-list/master/proxies.txt';

    public const SCHEDULE = '0 * * * *';
}

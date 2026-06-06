<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;
use IlmLV\ProxyScraper\ScraperInterface;

final class ProxiflyProxyList extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://cdn.jsdelivr.net/gh/proxifly/free-proxy-list@main/proxies/all/data.txt';

    const SCHEDULE = '*/30 * * * *';
}

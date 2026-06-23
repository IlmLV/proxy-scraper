<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class ProxiflyProxyList extends TextListScraper
{
    protected string $url = 'https://cdn.jsdelivr.net/gh/proxifly/free-proxy-list@main/proxies/all/data.txt';

    public const SCHEDULE = '*/30 * * * *';
}

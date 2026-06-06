<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;
use IlmLV\ProxyScraper\ScraperInterface;

final class ProxyScrapeComHttp extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://api.proxyscrape.com/v4/free-proxy-list/get?request=display_proxies&proxy_format=ipport&format=text&protocol=http';

    const SCHEDULE = '0 * * * *';
}

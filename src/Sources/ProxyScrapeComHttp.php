<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class ProxyScrapeComHttp extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://api.proxyscrape.com/v4/free-proxy-list/get?request=display_proxies&proxy_format=ipport&format=text&protocol=http';

    protected ?string $protocol = 'http';

    public const SCHEDULE = '0 * * * *';
}

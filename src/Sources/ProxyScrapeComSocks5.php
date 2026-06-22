<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class ProxyScrapeComSocks5 extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://api.proxyscrape.com/v4/free-proxy-list/get?request=display_proxies&proxy_format=ipport&format=text&protocol=socks5';

    protected ?string $protocol = 'socks5';

    public const SCHEDULE = '0 * * * *';
}

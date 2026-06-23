<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class ProxyScrapeComSocks4 extends TextListScraper
{
    protected string $url = 'https://api.proxyscrape.com/v4/free-proxy-list/get?request=display_proxies&proxy_format=ipport&format=text&protocol=socks4';

    protected ?string $protocol = 'socks4';

    public const SCHEDULE = '0 * * * *';
}

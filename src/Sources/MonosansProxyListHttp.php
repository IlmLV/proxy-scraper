<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class MonosansProxyListHttp extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/monosans/proxy-list/main/proxies/http.txt';

    protected ?string $protocol = 'http';

    public const SCHEDULE = '0 * * * *';
}

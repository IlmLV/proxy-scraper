<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class MonosansProxyListHttp extends TextListScraper
{
    protected string $url = 'https://raw.githubusercontent.com/monosans/proxy-list/main/proxies/http.txt';

    protected ?string $protocol = 'http';

    public const SCHEDULE = '0 * * * *';
}

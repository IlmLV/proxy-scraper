<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;
use IlmLV\ProxyScraper\ScraperInterface;

final class ShiftyTRProxyListHttp extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/ShiftyTR/Proxy-List/master/http.txt';

    protected ?string $protocol = 'http';

    const SCHEDULE = '0 * * * *';
}
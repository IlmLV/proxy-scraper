<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class VakhovFreshProxyListHttps extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/vakhov/fresh-proxy-list/master/https.txt';

    protected ?string $protocol = 'https';

    public const SCHEDULE = '0 * * * *';
}

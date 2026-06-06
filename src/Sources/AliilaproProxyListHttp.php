<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;
use IlmLV\ProxyScraper\ScraperInterface;

final class AliilaproProxyListHttp extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/ALIILAPRO/Proxy/main/http.txt';

    protected ?string $protocol = 'http';

    const SCHEDULE = '0 * * * *';
}

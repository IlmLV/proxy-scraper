<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class AliilaproProxyListHttp extends TextListScraper
{
    protected string $url = 'https://raw.githubusercontent.com/ALIILAPRO/Proxy/main/http.txt';

    protected ?string $protocol = 'http';

    public const SCHEDULE = '0 * * * *';
}

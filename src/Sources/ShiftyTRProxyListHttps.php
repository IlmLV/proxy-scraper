<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class ShiftyTRProxyListHttps extends TextListScraper
{
    protected string $url = 'https://raw.githubusercontent.com/ShiftyTR/Proxy-List/master/https.txt';

    protected ?string $protocol = 'https';

    public const SCHEDULE = '0 * * * *';
}

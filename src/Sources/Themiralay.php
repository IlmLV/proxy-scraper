<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class Themiralay extends TextListScraper
{
    protected string $url = 'https://raw.githubusercontent.com/themiralay/Proxy-List-World/master/data.txt';

    // The list is protocol-less (bare ip:port); default to http like CheckerProxyNet/Monosans.
    protected ?string $protocol = 'http';

    public const SCHEDULE = '0 * * * *';
}

<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;
use IlmLV\ProxyScraper\ScraperInterface;

final class RoosterkidOpenProxyListHttps extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/roosterkid/openproxylist/main/HTTPS_RAW.txt';

    protected ?string $protocol = 'https';

    const SCHEDULE = '0 * * * *';
}

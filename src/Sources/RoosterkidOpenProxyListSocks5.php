<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class RoosterkidOpenProxyListSocks5 extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/roosterkid/openproxylist/main/SOCKS5_RAW.txt';

    protected ?string $protocol = 'socks5';

    public const SCHEDULE = '0 * * * *';
}

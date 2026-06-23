<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class RoosterkidOpenProxyListSocks4 extends TextListScraper
{
    protected string $url = 'https://raw.githubusercontent.com/roosterkid/openproxylist/main/SOCKS4_RAW.txt';

    protected ?string $protocol = 'socks4';

    public const SCHEDULE = '0 * * * *';
}

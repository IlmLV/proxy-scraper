<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class VakhovFreshProxyListSocks4 extends TextListScraper
{
    protected string $url = 'https://raw.githubusercontent.com/vakhov/fresh-proxy-list/master/socks4.txt';

    protected ?string $protocol = 'socks4';

    public const SCHEDULE = '0 * * * *';
}

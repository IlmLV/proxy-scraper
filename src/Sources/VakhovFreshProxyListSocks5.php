<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class VakhovFreshProxyListSocks5 extends TextListScraper
{
    protected string $url = 'https://raw.githubusercontent.com/vakhov/fresh-proxy-list/master/socks5.txt';

    protected ?string $protocol = 'socks5';

    public const SCHEDULE = '0 * * * *';
}

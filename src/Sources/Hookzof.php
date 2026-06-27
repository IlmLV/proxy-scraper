<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class Hookzof extends TextListScraper
{
    protected string $url = 'https://raw.githubusercontent.com/hookzof/socks5_list/master/proxy.txt';

    protected ?string $protocol = 'socks5';

    public const SCHEDULE = '0 * * * *';
}

<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class TheSpeedXProxyListSocks4 extends TextListScraper implements ScraperInterface
{
    protected string $url = 'https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/socks4.txt';

    protected ?string $protocol = 'socks4';

    public const SCHEDULE = '0 * * * *';
}

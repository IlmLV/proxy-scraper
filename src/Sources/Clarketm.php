<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TextListScraper;

final class Clarketm extends TextListScraper
{
    protected string $url = 'https://raw.githubusercontent.com/clarketm/proxy-list/master/proxy-list-raw.txt';

    protected ?string $protocol = 'http';

    public const SCHEDULE = '0 0 * * *';
}

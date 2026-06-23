<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TableListScraper;

final class FreeProxyListNetUkProxy extends TableListScraper
{
    protected string $url = 'https://free-proxy-list.net/uk-proxy.html';

    protected ?string $protocol = 'http';
    protected string $rowPath = '#list tbody tr';
    protected int $colAddress = 0;
    protected int $colPort = 1;

    public const SCHEDULE = '*/10 * * * *';
}

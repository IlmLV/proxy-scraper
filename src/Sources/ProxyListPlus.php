<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TableListScraper;

final class ProxyListPlus extends TableListScraper
{
    protected string $url = 'https://list.proxylistplus.com/Fresh-HTTP-Proxy-List-1';

    protected ?string $protocol = 'http';
    protected string $rowPath = 'table.bg tr.cells';
    protected int $colAddress = 1;
    protected int $colPort = 2;

    public const SCHEDULE = '0 * * * *';
}

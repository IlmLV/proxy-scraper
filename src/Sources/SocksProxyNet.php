<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TableListScraper;

final class SocksProxyNet extends TableListScraper implements ScraperInterface
{
    protected string $url = 'https://www.socks-proxy.net/';

    protected string $rowPath = '#list tbody tr';
    protected int $colAddress = 0;
    protected int $colPort = 1;
    protected int $colProtocol = 4;

    public const SCHEDULE = '*/10 * * * *';
}

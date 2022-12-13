<?php

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TableListScraper;

final class FreeProxyListNetUkProxy extends TableListScraper implements ScraperInterface
{
    protected string $url = 'https://free-proxy-list.net/uk-proxy.html';

    protected ?string $protocol = 'http';
    protected string $rowPath = '#list tbody tr';
    protected int $colAddress = 0;
    protected int $colPort = 1;

    const SCHEDULE = '*/10 * * * *';
}
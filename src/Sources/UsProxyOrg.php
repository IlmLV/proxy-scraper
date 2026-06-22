<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TableListScraper;

final class UsProxyOrg extends TableListScraper implements ScraperInterface
{
    protected string $url = 'https://www.us-proxy.org/';

    protected ?string $protocol = 'http';
    protected string $rowPath = '#list tbody tr';
    protected int $colAddress = 0;
    protected int $colPort = 1;

    public const SCHEDULE = '*/10 * * * *';
}

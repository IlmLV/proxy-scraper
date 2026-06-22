<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TableListScraper;

final class Proxy11 extends TableListScraper implements ScraperInterface
{
    protected string $url = 'http://proxy11.com/free-proxy';

    protected ?string $protocol = 'http';
    protected string $rowPath = 'table.table-hover tbody tr';
    protected int $colAddress = 0;
    protected int $colPort = 1;

    public const SCHEDULE = '0 * * * *';
}

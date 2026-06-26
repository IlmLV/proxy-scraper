<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TableListScraper;

/**
 * freeproxy.world lists mixed protocols in one table; the "Type" column (index 5)
 * holds a clean http/https/socks4/socks5 token, so we read the protocol per row.
 */
final class FreeProxyWorld extends TableListScraper
{
    protected string $url = 'https://www.freeproxy.world/';

    protected ?string $protocol = null;
    protected string $rowPath = 'table.table tbody tr';
    protected int $colAddress = 0;
    protected int $colPort = 1;
    protected int $colProtocol = 5;

    public const SCHEDULE = '0 * * * *';
}

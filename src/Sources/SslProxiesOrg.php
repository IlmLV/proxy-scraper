<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use IlmLV\ProxyScraper\Scrapers\TableListScraper;

final class SslProxiesOrg extends TableListScraper
{
    protected string $url = 'https://www.sslproxies.org/';

    // sslproxies.org publishes the free-proxy-list.net "#list" table filtered to
    // rows whose "Https" column is "yes". That column is an HTTPS-capability flag,
    // not the proxy's own scheme — the listed entries are HTTP proxies.
    protected ?string $protocol = 'http';
    protected string $rowPath = '#list tbody tr';
    protected int $colAddress = 0;
    protected int $colPort = 1;

    public const SCHEDULE = '*/10 * * * *';
}

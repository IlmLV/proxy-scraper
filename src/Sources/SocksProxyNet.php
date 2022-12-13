<?php

namespace IlmLV\ProxyScraper\Sources;

use Generator;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\TableListScraper;
use Symfony\Component\DomCrawler\Crawler as Dom;


final class SocksProxyNet extends TableListScraper implements ScraperInterface
{
    protected string $url = 'https://www.socks-proxy.net/';

    protected string $rowPath = '#list tbody tr';
    protected int $colAddress = 0;
    protected int $colPort = 1;
    protected int $colProtocol = 4;

    const SCHEDULE = '*/10 * * * *';
}
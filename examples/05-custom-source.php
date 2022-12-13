<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\JsonScrapper;

class CustomGimmeProxy extends JsonScrapper implements ScraperInterface
{
    protected string $url = 'https://gimmeproxy.com/api/getProxy';
}

$proxies = LoadProxies::init()
    ->only(CustomGimmeProxy::class);

dump($proxies->stats());

foreach ($proxies->get() as $proxy) {
    echo $proxy . PHP_EOL;
}
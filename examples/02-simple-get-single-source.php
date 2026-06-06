<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\Sources\FreeProxyListNet;

$proxies = LoadProxies::init()
    ->only(FreeProxyListNet::class);

dump($proxies->stats());

foreach ($proxies->get() as $proxy) {
    echo $proxy . PHP_EOL;
}
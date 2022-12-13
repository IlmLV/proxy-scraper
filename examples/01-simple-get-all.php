<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IlmLV\ProxyScraper\LoadProxies;

$proxies = LoadProxies::init()
    ->all();

foreach ($proxies->get() as $proxy) {
    echo $proxy . PHP_EOL;
}

dump($proxies->stats());
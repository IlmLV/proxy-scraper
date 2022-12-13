<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IlmLV\ProxyScraper\LoadProxies;

$proxies = LoadProxies::init()
    ->all();

dump($proxies->stats());

foreach ($proxies->stats() as $source => $results) {
    echo $source . ' => ' . json_encode($results) . PHP_EOL;
}
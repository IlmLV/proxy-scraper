<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\Sources\GimmeProxyCom;

$scraperConfig = [
    GimmeProxyCom::class => [
        'api_key' => 'wrong_key',
        'protocol' => 'wrong_protocol',
        'wrongParameter' => 'wrong_value'
    ]
];

$proxies = LoadProxies::init($scraperConfig)
    ->only(GimmeProxyCom::class);

dump($proxies->stats());

foreach ($proxies->errors() as $scraper => $exception) {
    echo $scraper . ' => ' . $exception->getMessage() . PHP_EOL;
}

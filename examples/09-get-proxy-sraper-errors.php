<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\Sources\PubProxyCom;

$scraperConfig = [
    PubProxyCom::class => [
        'api' => 'wrong_key',
        'level' => 'wrong_level',
        'wrongParameter' => 'wrong_value'
    ]
];

$proxies = LoadProxies::init($scraperConfig)
    ->only(PubProxyCom::class);

dump($proxies->stats());

foreach ($proxies->errors() as $scraper => $exception) {
    echo $scraper . ' => ' . $exception->getMessage() . PHP_EOL;
}

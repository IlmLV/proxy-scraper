<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\Sources\PubProxyCom;
use Symfony\Component\HttpClient\HttpClient;

// Extra keys are appended to the source URL as query parameters,
// so they can be used to tune sources that accept them (e.g. pubproxy.com).
$scraperConfig = [
    PubProxyCom::class => [
        //'api' => 'xxx',
        'country' => 'US',
        'https' => true,
        'level' => 'elite'
    ]
];

$httpClient = HttpClient::create([
    'timeout' => 30,
]);

$proxies = LoadProxies::init($scraperConfig, $httpClient)
    ->only(PubProxyCom::class);

dump($proxies->stats());

foreach ($proxies->get() as $proxy) {
    echo $proxy . PHP_EOL;
}
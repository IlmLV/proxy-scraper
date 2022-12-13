<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\Sources\GimmeProxyCom;
use Symfony\Component\HttpClient\HttpClient;

$scraperConfig = [
    GimmeProxyCom::class => [
        //'api_key' => 'xxx',
        'protocol' => 'socks5',
        'supportsHttps' => true,
        'maxCheckPeriod' => 600
    ]
];

$httpClient = HttpClient::create([
    'timeout' => 30,
]);

$proxies = LoadProxies::init($scraperConfig, $httpClient)
    ->only(GimmeProxyCom::class);

dump($proxies->stats());

foreach ($proxies->get() as $proxy) {
    echo $proxy . PHP_EOL;
}
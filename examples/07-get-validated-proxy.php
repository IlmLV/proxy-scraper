<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IlmLV\ProxyScraper\Validators\ProxyValidator;
use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\Sources\GimmeProxyCom;

$proxies = LoadProxies::init()
    ->only(GimmeProxyCom::class);

dump($proxies->stats());

foreach ($proxies->get() as $proxy) {
    echo $proxy . PHP_EOL;

    $validator = (new ProxyValidator('socks5://x6165799:LAtj64mAzT@proxy-nl.privateinternetaccess.com:1080'))->validate();
    dump(json_decode(json_encode($validator)));
}

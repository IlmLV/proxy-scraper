<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IlmLV\ProxyScraper\Validators\ProxyValidator;

$proxy = new \IlmLV\ProxyScraper\Entities\Proxy('http://75.126.253.8:8080');

$validator = (new ProxyValidator($proxy))->validate();
dump(json_decode(json_encode($validator)));

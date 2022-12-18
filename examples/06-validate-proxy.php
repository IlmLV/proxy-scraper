<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IlmLV\ProxyScraper\Validations\ProxyValidation;

$proxy = new \IlmLV\ProxyScraper\Entities\Proxy('http://75.126.253.8:8080');

$validation = new ProxyValidation($proxy);
dump(json_decode(json_encode($validation)));

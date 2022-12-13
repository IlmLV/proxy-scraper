<?php

namespace IlmLV\ProxyScraper\Validators;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DomainsValidator
{
    private array $domains = [
        \IlmLV\ProxyScraper\Validators\Domains\AmazonCom::class,
        \IlmLV\ProxyScraper\Validators\Domains\CraigslistOrg::class,
        \IlmLV\ProxyScraper\Validators\Domains\ExampleCom::class,
        \IlmLV\ProxyScraper\Validators\Domains\GoogleCom::class,
        \IlmLV\ProxyScraper\Validators\Domains\SsCom::class,
    ];

    public function __construct(HttpClientInterface $client = null)
    {
        foreach ($this->domains as $validator) {
            $this->{$validator::NAME} = new $validator($client);
        }
    }
}
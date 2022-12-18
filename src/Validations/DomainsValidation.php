<?php

namespace IlmLV\ProxyScraper\Validations;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DomainsValidation
{
    private array $domains = [
        \IlmLV\ProxyScraper\Validations\Domains\AmazonCom::class,
        \IlmLV\ProxyScraper\Validations\Domains\CraigslistOrg::class,
        \IlmLV\ProxyScraper\Validations\Domains\ExampleCom::class,
        \IlmLV\ProxyScraper\Validations\Domains\GoogleCom::class,
        \IlmLV\ProxyScraper\Validations\Domains\SsCom::class,
    ];

    public function __construct(HttpClientInterface $client = null)
    {
        foreach ($this->domains as $validator) {
            $this->{$validator::NAME} = new $validator($client);
        }
    }
}
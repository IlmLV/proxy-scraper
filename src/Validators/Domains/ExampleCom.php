<?php

namespace IlmLV\ProxyScraper\Validators\Domains;

use IlmLV\ProxyScraper\Entities\ResponseError;
use IlmLV\ProxyScraper\Validators\RequestValidator;
use Symfony\Component\DomCrawler\Crawler as Dom;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExampleCom extends RequestValidator
{
    const NAME = 'example.com';
    const METHOD = 'GET';
    const URL = 'http://example.com/';

    public function __construct(HttpClientInterface $client = null)
    {
        return parent::__construct(self::METHOD, self::URL, $client);
    }

    public function validate(): bool
    {
        try {
            $response = $this->request($this->method, $this->url);

            $dom = (new Dom($response->getContent()));
            $title = $dom->filter('title');

            return $response->getStatusCode() === 200
                && $title->text() === 'Example Domain';
        }
        catch (\Throwable $e) {
            $this->error = new ResponseError($e);
            return false;
        }
    }
}
<?php

namespace IlmLV\ProxyScraper\Validations\Domains;

use IlmLV\ProxyScraper\Entities\ResponseError;
use IlmLV\ProxyScraper\Validations\AbstractRequestValidation;
use Symfony\Component\DomCrawler\Crawler as Dom;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AmazonCom extends AbstractRequestValidation
{
    const NAME = 'amazon.com';
    const METHOD = 'GET';
    const URL = 'https://www.amazon.com/';

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
                && strpos($title->text(), 'Amazon.com.') !== false;
        }
        catch (\Throwable $e) {
            $this->error = new ResponseError($e);
            return false;
        }
    }
}
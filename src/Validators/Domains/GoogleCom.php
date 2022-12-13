<?php

namespace IlmLV\ProxyScraper\Validators\Domains;

use IlmLV\ProxyScraper\Entities\ResponseError;
use IlmLV\ProxyScraper\Validators\RequestValidator;
use Symfony\Component\DomCrawler\Crawler as Dom;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleCom extends RequestValidator
{
    const NAME = 'google.com';
    const METHOD = 'GET';
    const URL = 'https://www.google.com/search?q=site%3Aserviss.it';

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
                && strpos($title->text(), 'Google Search') !== false
                && strpos($title->text(), 'Sorry...') === false
                && strpos($dom->text(), 'e=document.getElementById(\'captcha\');if(e){e.focus();}') === false;
        }
        catch (\Throwable $e) {
            $this->error = new ResponseError($e);
            return false;
        }
    }
}
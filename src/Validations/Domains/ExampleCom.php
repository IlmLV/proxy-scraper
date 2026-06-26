<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations\Domains;

use IlmLV\ProxyScraper\Entities\ResponseError;
use Symfony\Component\DomCrawler\Crawler as Dom;

class ExampleCom extends AbstractDomainValidation
{
    public const NAME = 'example.com';
    public const URL = 'http://example.com/';

    public function validate(): bool
    {
        try {
            $response = $this->request($this->method, $this->url);

            $dom = (new Dom($response->getContent()));
            $title = $dom->filter('title');

            return $response->getStatusCode() === 200
                && $title->text() === 'Example Domain';
        } catch (\Throwable $e) {
            $this->error = new ResponseError($e);
            return false;
        }
    }
}

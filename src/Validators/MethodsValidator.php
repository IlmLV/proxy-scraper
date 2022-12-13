<?php

namespace IlmLV\ProxyScraper\Validators;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MethodsValidator
{
    private array $requestMethods = [
        'GET',
        'POST',
        'PUT',
        'OPTIONS',
        'HEAD',
        'DELETE',
        'PATCH',
    ];

    public HeadersValidator $get;
    public HeadersValidator $post;
    public HeadersValidator $put;
    public HeadersValidator $options;
    public HeadersValidator $head;
    public HeadersValidator $delete;
    public HeadersValidator $patch;

    public function __construct(string $url, HttpClientInterface $client = null)
    {
        foreach ($this->requestMethods as $method) {
            $this->{strtolower($method)} = new HeadersValidator($method, $url, $client);
        }
    }
}
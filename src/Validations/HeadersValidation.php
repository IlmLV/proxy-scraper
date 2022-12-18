<?php

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Entities\ResponseError;

class HeadersValidation extends AbstractRequestValidation
{
    private array $headerValues = [
        'common' => [
            'A-IM' => 'feed',
            'Accept' => 'application/json',
            'Accept-Charset' => 'utf-8',
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'en-US',
            'Accept-Datetime' => 'Thu, 18 Mar 1993 18:03:19 GMT',
            'Access-Control-Request-Method' => 'GET',
            'Access-Control-Request-Headers' => 'origin, x-requested-with, accept',
            'Authorization' => 'Basic c2Vydmlzcy5pdDpzZXJ2aXNzLml0Cg==',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Cookie' => 'name=value',
            'Date' => 'Thu, 18 Mar 1993 18:03:19 GMT',
            'Expect' => '100-continue',
            'Forwarded' => 'for=192.0.2.60; proto=http; by=91.203.69.0',
            'From' => 'user@example.com',
            'If-Modified-Since' => 'Thu, 18 Mar 1993 18:03:19 GMT',
            'If-None-Match' => '"737060cd882f209582d"',
            'If-Range' => '"737060cd8c9582d"',
            'Max-Forwards' => '10',
            'Origin' => 'http://serviss.it',
            'Pragma' => 'no-cache',
            'Range' => 'bytes=500-999',
            'Referer' => 'https://serviss.it',
            'TE' => 'trailers, deflate',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
            'Upgrade' => 'h2c, HTTPS/1.3, IRC/6.9, RTA/x11, websocket',
            'Via' => '1.0 fred, 1.1 example.com (Apache/1.1)',
            'Warning' => '199 Miscellaneous warning',
            'DNT' => '1',
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-Token' => 'c2Vydmlzcy5pdDpzZXJ2aXNzLml0Cg==',
            'X-Real-Ip' => '91.203.69.0',
            'X-Proxy-Id' => '01',
            'X-Forwarded' => 'for=192.0.2.60; proto=http; by=91.203.69.0',
            'X-Forwarded-For' => '91.203.69.0, 91.203.0.0',
            'Forwarded-For' => '91.203.69.0, 91.203.0.0',
            'Forwarded-For-Ip' => '91.203.69.0',
            'Client-Ip' => '91.203.69.0',
            'X-Client-Ip' => '91.203.69.0',
        ],
        'GET' => [],
        'POST' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
        'PUT' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
        'OPTIONS' => [],
        'HEAD' => [],
        'DELETE' => [],
        'PATCH' => [],
    ];
    public array $headers = [];

    /**
     * @var ResponseError
     */
    public ResponseError $error;

    public function validate(): bool
    {
        try {
            $requestHeaders = $this->headerValues['common'] + $this->headerValues[$this->method];

            $response = $this->request($this->method, $this->url, ['headers' => $requestHeaders]);

            $body = $response->getContent();

            if ($this->method === 'HEAD') {
                return $response->getStatusCode() === 200 && $body === '';
            } else {
                $responseAttr = json_decode($response->getContent(), true);
                foreach($requestHeaders as $key => $value) {
                    $responseKey = kebabToSnake(strtolower($key));
                    $this->headers[$key] = isset($responseAttr[$responseKey]) ? ($responseAttr[$responseKey] === $value ? true : false) : false;
                }

                return $response->getStatusCode() === 200 && $responseAttr['method'] === $this->method;
            }
        }
        catch (\Throwable $e) {
            $this->error = new ResponseError($e);
            return false;
        }
    }
}
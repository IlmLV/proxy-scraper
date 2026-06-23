<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Arr;
use IlmLV\ProxyScraper\Entities\ResponseError;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Single-URL egress reachability check: requests one endpoint through the
 * proxy and reports whether it was reachable. Unlike {@see IpValidation} it
 * does not require the returned IP to equal the proxy host (the egress IP for
 * an IPv4-only / IPv6-only endpoint generally differs) — it only confirms the
 * proxy could route a request to that address family.
 */
class EgressValidation extends AbstractRequestValidation
{
    /**
     * @var string|null The egress IP the endpoint reported, if any.
     */
    public ?string $ip = null;

    public function __construct(string $url, ?HttpClientInterface $client = null)
    {
        parent::__construct('GET', $url, $client);
    }

    public static function make(string $url, ?HttpClientInterface $client = null): self
    {
        return new self($url, $client);
    }

    public function validate(): bool
    {
        try {
            $response = $this->request($this->method, $this->url);
            $body = json_decode($response->getContent(), true);

            $ip = Arr::get($body, 'ip');
            $this->ip = is_string($ip) ? $ip : null;

            return $response->getStatusCode() === 200 && is_string($ip);
        } catch (\Throwable $e) {
            $this->error = new ResponseError($e);
            return false;
        }
    }
}

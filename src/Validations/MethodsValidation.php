<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

use JsonSerializable;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Runs a {@see HeadersValidation} per HTTP method and exposes each result as a
 * read-only property named after the lowercased method (e.g. `$methods->get`).
 * Results are stored in a keyed array rather than fixed typed properties so an
 * arbitrary $requestMethods list can be supplied without creating dynamic
 * properties or leaving declared ones uninitialised.
 *
 * @property-read HeadersValidation|null $get
 * @property-read HeadersValidation|null $post
 * @property-read HeadersValidation|null $put
 * @property-read HeadersValidation|null $options
 * @property-read HeadersValidation|null $head
 * @property-read HeadersValidation|null $delete
 * @property-read HeadersValidation|null $patch
 */
class MethodsValidation implements JsonSerializable
{
    use KeyedResultMap;

    /**
     * @var string[]
     */
    private array $requestMethods = [
        'GET',
        'POST',
        'PUT',
        'OPTIONS',
        'HEAD',
        'DELETE',
        'PATCH',
    ];

    /**
     * @var array<string, HeadersValidation> Keyed by lowercased method name.
     */
    private array $methods = [];

    private string $url;

    private ?HttpClientInterface $client;

    public ?float $latency = null;

    public function __construct(string $url, ?HttpClientInterface $client = null)
    {
        $this->url = $url;
        $this->client = $client;
    }

    public static function make(string $url, ?HttpClientInterface $client = null): self
    {
        return new self($url, $client);
    }

    /**
     * Restrict the HTTP methods probed (defaults to all seven). Set before run();
     * a change afterwards applies only to a subsequent run().
     *
     * @param string[] $requestMethods
     */
    public function setRequestMethods(array $requestMethods): self
    {
        $this->requestMethods = $requestMethods;

        return $this;
    }

    /**
     * Run a HeadersValidation per configured method, average the latency of the
     * ones that passed, and return $this. Construction performs no I/O.
     */
    public function run(): self
    {
        $latencySum = 0.0;
        $latencyCount = 0;

        foreach ($this->requestMethods as $method) {
            $validation = HeadersValidation::make($method, $this->url, $this->client)->run();
            $this->methods[strtolower($method)] = $validation;

            if ($validation->valid && $validation->latency !== null) {
                $latencySum += $validation->latency;
                $latencyCount++;
            }
        }

        $this->latency = $latencyCount > 0 ? $latencySum / $latencyCount : null;

        return $this;
    }

    /**
     * @return array<string, HeadersValidation>
     */
    protected function resultMap(): array
    {
        return $this->methods;
    }

    /**
     * Serialise as "latency" followed by one entry per tested method, preserving
     * the historical JSON shape (e.g. {"latency": ..., "get": {...}, ...}).
     *
     * @return array<string, float|HeadersValidation|null>
     */
    public function jsonSerialize(): array
    {
        return ['latency' => $this->latency] + $this->methods;
    }
}

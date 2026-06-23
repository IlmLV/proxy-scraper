<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Entities\ResponseError;
use IlmLV\ProxyScraper\Exceptions\ValidatorException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AnonymityLevelValidation
{
    public const URL = ValidationEndpoints::WHOAMI_HTTP;

    public const ELITE = 'elite';
    public const ANONYMOUS = 'anonymous';
    public const EXPOSED = 'exposed';

    /** Echo-response keys (snake_cased) that, present, betray the use of a proxy. */
    private const PROXY_HEADERS = ['x_real_ip', 'via', 'client_ip', 'xroxy_connection'];

    /** Substrings in an echo-response key that betray the use of a proxy. */
    private const PROXY_HEADER_PREFIXES = ['proxy', 'x_proxy', 'forwarded', 'x_forwarded'];

    protected HttpClientInterface $client;

    private Host $hostIp;

    public ?string $anonymityLevel = null;

    public ?ResponseError $error = null;

    public function __construct(Host $hostIp, ?HttpClientInterface $client = null)
    {
        $this->hostIp = $hostIp;
        $this->client = $client ?? HttpClient::create();
    }

    public static function make(Host $hostIp, ?HttpClientInterface $client = null): self
    {
        return new self($hostIp, $client);
    }

    /**
     * Determine the anonymity level (populating $anonymityLevel / $error) and
     * return $this. Construction performs no I/O.
     */
    public function run(): self
    {
        $this->anonymityLevel = $this->anonymityLevel();

        return $this;
    }

    /**
     * @throws ValidatorException
     */
    public function __toString(): string
    {
        if (!$this->anonymityLevel) {
            throw new ValidatorException((string) $this->error);
        }

        return $this->anonymityLevel;
    }

    public function anonymityLevel(): ?string
    {
        try {
            $response = $this->client->request('GET', self::URL);
            $content = $response->getContent();
            $body = json_decode($content, true);

            if (!is_array($body) || $body === []) {
                throw new ValidatorException($this->describeEmptyResponse($content, $response->getStatusCode()));
            }

            unset($body['server_ip']);

            // Our real IP appearing in the echo (or a non-200) means it leaked.
            if ($response->getStatusCode() !== 200 || in_array((string) $this->hostIp, $body, true)) {
                return self::EXPOSED;
            }

            // Real IP hidden: elite unless the proxy still announced itself via a header.
            return $this->hasProxyHeader($body) ? self::ANONYMOUS : self::ELITE;
        } catch (\Throwable $e) {
            $this->error = new ResponseError($e);
            return null;
        }
    }

    /**
     * @param array<array-key, mixed> $body
     */
    private function hasProxyHeader(array $body): bool
    {
        foreach (array_keys($body) as $attr) {
            if (in_array($attr, self::PROXY_HEADERS, true)) {
                return true;
            }
            foreach (self::PROXY_HEADER_PREFIXES as $prefix) {
                if (strpos((string) $attr, $prefix) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function describeEmptyResponse(string $content, int $statusCode): string
    {
        foreach (['Please wait', 'verified', 'verification', 'verify'] as $needle) {
            if (strpos($content, $needle) !== false) {
                return 'Failed to load response. Verification failed. http_status=' . $statusCode;
            }
        }

        return 'Failed to load response, http_status=' . $statusCode;
    }
}

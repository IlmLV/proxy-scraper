<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Entities\ResponseError;
use IlmLV\ProxyScraper\Exceptions\ValidatorException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AnonymityLevelValidation extends AbstractRequestValidation
{
    public const URL = ValidationEndpoints::WHOAMI_HTTP;

    public const ELITE = 'elite';
    public const ANONYMOUS = 'anonymous';
    public const EXPOSED = 'exposed';

    /** Echo-response keys (snake_cased) that, present, betray the use of a proxy. */
    private const PROXY_HEADERS = ['x_real_ip', 'via', 'client_ip', 'xroxy_connection'];

    /** Substrings in an echo-response key that betray the use of a proxy. */
    private const PROXY_HEADER_PREFIXES = ['proxy', 'x_proxy', 'forwarded', 'x_forwarded'];

    private Host $hostIp;

    public ?string $anonymityLevel = null;

    public function __construct(Host $hostIp, ?HttpClientInterface $client = null)
    {
        $this->hostIp = $hostIp;

        parent::__construct('GET', self::URL, $client);
    }

    public static function make(Host $hostIp, ?HttpClientInterface $client = null): self
    {
        return new self($hostIp, $client);
    }

    /**
     * Probe the echo endpoint and classify the proxy's anonymity, populating
     * $anonymityLevel (elite/anonymous/exposed, or null on failure). Returns
     * whether the proxy hides the real IP — an exposed proxy or a failed probe
     * is not "valid". Invoked by the inherited run().
     */
    public function validate(): bool
    {
        try {
            $response = $this->request('GET', self::URL);
            $content = $response->getContent();
            $body = json_decode($content, true);

            if (!is_array($body) || $body === []) {
                throw new ValidatorException($this->describeEmptyResponse($content, $response->getStatusCode()));
            }

            unset($body['server_ip']);

            // Our real IP appearing in the echo (or a non-200) means it leaked.
            if ($response->getStatusCode() !== 200 || in_array((string) $this->hostIp, $body, true)) {
                $this->anonymityLevel = self::EXPOSED;
            } else {
                // Real IP hidden: elite unless the proxy still announced itself via a header.
                $this->anonymityLevel = $this->hasProxyHeader($body) ? self::ANONYMOUS : self::ELITE;
            }

            return $this->anonymityLevel !== self::EXPOSED;
        } catch (\Throwable $e) {
            $this->error = new ResponseError($e);
            return false;
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

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
    public const URL = 'http://whoami.serviss.it/?format=json';

    protected HttpClientInterface $client;

    private Host $hostIp;

    public ?string $anonymityLevel;

    public ?ResponseError $error = null;

    public function __construct(Host $hostIp, ?HttpClientInterface $client = null)
    {
        $this->hostIp = $hostIp;
        $this->client = $client ?? HttpClient::create();

        $this->anonymityLevel = $this->anonymityLevel();
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
            $body = json_decode($response->getContent(), true);

            if (!is_array($body) || $body === []) {
                if (
                    strpos($response->getContent(), 'Please wait') !== false
                    || strpos($response->getContent(), 'verified') !== false
                    || strpos($response->getContent(), 'verification') !== false
                    || strpos($response->getContent(), 'verify') !== false
                ) {
                    throw new \Exception('Failed to load response. Verification failed. http_status=' . $response->getStatusCode());
                } else {
                    throw new \Exception('Failed to load response, http_status=' . $response->getStatusCode());
                }
            }

            unset($body['server_ip']);

            $level = 'exposed';
            if ($response->getStatusCode() === 200 && !in_array((string)$this->hostIp, $body)) {
                $level = 'anonymous';
                $hasProxyHeader = call_user_func(function () use ($body): bool {
                    $proxyHeaders = [
                        'x_real_ip',
                        'via',
                        'client_ip',
                        'xroxy_connection',
                    ];
                    $proxyHeaderPrefixes = [
                        'proxy',
                        'x_proxy',
                        'forwarded',
                        'x_forwarded',
                    ];
                    foreach (array_keys($body) as $attr) {
                        if (in_array($attr, $proxyHeaders)) {
                            return true;
                        }
                        foreach ($proxyHeaderPrefixes as $prefix) {
                            if (strpos((string)$attr, $prefix) !== false) {
                                return true;
                            }
                        }
                    }
                    return false;
                });
                if (!$hasProxyHeader) {
                    $level = 'elite';
                }
            }
            return $level;
        } catch (\Throwable $e) {
            $this->error = new ResponseError($e);
            return null;
        }
    }
}

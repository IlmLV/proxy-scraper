<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Entities\Port;
use IlmLV\ProxyScraper\Entities\Protocol;
use IlmLV\ProxyScraper\Entities\Proxy;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProxyScraper
{
    protected string $url;

    /**
     * @var array<string, mixed>
     */
    protected array $options;

    /** @var string */
    const SCHEDULE = '* * * * *';

    /**
     * @var HttpClientInterface
     */
    protected HttpClientInterface $httpClient;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(HttpClientInterface $httpClient, array $options = [])
    {
        $this->httpClient = $httpClient;

        $this->loadOptions($options);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function loadOptions(array &$options = []): void
    {
        foreach ($options as $key => $value) {
            $methodName = snakeToCamel('set_'. $key);
            if (method_exists($this, $methodName)) {
                $this->{$methodName}($value);

                unset($options[$key]);
            }
        }

        $this->options = $this->processOptions($options);
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function processOptions(array $options): array
    {
        // cast all booleans as string
        return array_map(function($a) {
            if (is_bool($a)) {
                return $a === true ? 'true' : 'false';
            }
            return $a;
        }, $options);
    }

    /**
     * @return string
     */
    protected function getUrl(string ...$values): string
    {
        return sprintf($this->url . ($this->options ? '?' . http_build_query($this->options) : ''), ...$values);
    }

    /**
     * @param string $ip
     * @param int|string $port
     * @param string $protocol
     * @return Proxy
     * @throws Exceptions\InvalidArgumentException
     */
    protected function makeProxy(string $ip, int|string $port, string $protocol): Proxy
    {
        return new Proxy(new Protocol($protocol), new Host($ip), new Port($port));
    }
}
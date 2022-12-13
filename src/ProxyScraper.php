<?php

namespace IlmLV\ProxyScraper;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Entities\Port;
use IlmLV\ProxyScraper\Entities\Protocol;
use IlmLV\ProxyScraper\Entities\Proxy;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProxyScraper
{
    protected string $url;

    protected array $options;

    const SCHEDULE = '* * * * *';

    /**
     * @var HttpClientInterface
     */
    protected HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient, array $options = [])
    {
        $this->httpClient = $httpClient;

        $this->loadOptions($options);
    }

    private function loadOptions(&$options = []): void
    {
        foreach ($options as $key => $value) {
            $methodName = snakeToCamel('set_'. $key);
            if (method_exists($this, $methodName)) {
                call_user_func(array($this, $methodName), $value);

                unset($options[$key]);
            }
        }

        $this->options = $this->processOptions($options);
    }

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
     * @param $ip
     * @param $port
     * @param $protocol
     * @return Proxy
     * @throws Exceptions\InvalidArgumentException
     */
    protected function makeProxy($ip, $port, $protocol): Proxy
    {
        return new Proxy(new Protocol($protocol), new Host($ip), new Port($port));
    }
}
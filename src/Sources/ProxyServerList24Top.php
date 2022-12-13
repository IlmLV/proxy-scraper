<?php

namespace IlmLV\ProxyScraper\Sources;

use Generator;
use IlmLV\ProxyScraper\Entities\Protocol;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\ScraperInterface;

final class ProxyServerList24Top extends ProxyScraper implements ScraperInterface
{
    protected string $url = 'http://www.proxyserverlist24.top/feeds/posts/default';

    protected ?string $protocol = 'http';

    const SCHEDULE = '0 * * * *';

    /**
     * @return Generator
     * @throws ScraperException
     */
    public function get(): Generator
    {
        try {
            $html = $this->httpClient->request('GET', $this->url)->getContent();

            if (strpos($html, '<?xml') === false) {
                throw new ScraperException('Invalid XML');
            }

            $dom = simplexml_load_string($html);

        } catch (\Exception|\Throwable $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        foreach ($dom->entry ?? [] as $node) {
            $matches = [];
            preg_match_all('/((('. implode('|', Protocol::ALLOWED_PROTOCOLS) .'):\/\/)?\d+\.\d+\.\d+\.\d+([:\t])\d{1,5})/m', (string)$node->content, $matches);

            foreach ($matches[0] as $proxyString) {
                try {
                    $proxyString = str_replace("\t", ':', $proxyString);
                    yield new Proxy(($this->protocol ? $this->protocol . '://' : '') . $proxyString);
                } catch (InvalidArgumentException $e) {
                    continue;
                }
            }
        }
    }
}
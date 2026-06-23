<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use Generator;
use IlmLV\ProxyScraper\Entities\Protocol;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\ScraperInterface;

final class BlogspotProxyCom extends ProxyScraper implements ScraperInterface
{
    protected string $url = 'https://blogspotproxy.blogspot.com/feeds/posts/default';

    protected ?string $protocol = 'http';

    public const SCHEDULE = '0 * * * *';

    /**
     * @throws ScraperException
     */
    public function get(): Generator
    {
        $html = $this->fetch();

        if (strpos($html, '<?xml') === false) {
            throw new ScraperException('Invalid XML');
        }

        $dom = @simplexml_load_string($html);
        if ($dom === false) {
            throw new ScraperException('Failed to parse XML feed');
        }

        foreach ($dom->entry ?? [] as $node) {
            $matches = [];
            $schemes = implode('|', array_map(static fn (Protocol $p): string => $p->value, Protocol::cases()));
            preg_match_all('/(('. $schemes .'):\/\/)?\d+\.\d+\.\d+\.\d+([:\t])\d{1,5}/m', (string)$node->content, $matches);

            foreach ($matches[0] as $proxyString) {
                try {
                    $proxyString = str_replace("\t", ':', $proxyString);
                    yield Proxy::fromString(($this->protocol ? $this->protocol . '://' : '') . $proxyString);
                } catch (InvalidArgumentException $e) {
                    continue;
                }
            }
        }
    }
}

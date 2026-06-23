<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Sources;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Exceptions\ScraperException;
use IlmLV\ProxyScraper\ProxyScraper;
use IlmLV\ProxyScraper\ScraperInterface;

/**
 * spys.me runs its own scanner and serves a whitespace-separated list:
 * "IP:PORT CC-Anonymity-SSL[!] [+]" preceded by a few banner/legend lines.
 * We take the first token of each line as ip:port; banner lines fail the Proxy
 * parse and are skipped automatically. The list is HTTP(S) proxies.
 */
final class SpysMeProxyList extends ProxyScraper implements ScraperInterface
{
    protected string $url = 'https://spys.me/proxy.txt';

    protected string $protocol = 'http';

    public const SCHEDULE = '0 * * * *';

    /**
     * @return Generator<int, Proxy>
     * @throws ScraperException
     */
    public function get(): Generator
    {
        $text = $this->fetch();

        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = preg_split('/\s+/', $line);
            if (!is_array($parts)) {
                continue;
            }
            $address = $parts[0];

            try {
                $proxy = Proxy::fromString($this->protocol . '://' . $address);
            } catch (InvalidArgumentException $e) {
                continue;
            }

            yield $proxy;
        }
    }
}

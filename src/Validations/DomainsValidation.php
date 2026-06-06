<?php

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Validations\Domains\AmazonCom;
use IlmLV\ProxyScraper\Validations\Domains\CraigslistOrg;
use IlmLV\ProxyScraper\Validations\Domains\ExampleCom;
use IlmLV\ProxyScraper\Validations\Domains\GoogleCom;
use IlmLV\ProxyScraper\Validations\Domains\SsCom;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DomainsValidation implements \JsonSerializable
{
    /**
     * Validator classes to run. Each is stored in $validators keyed by its
     * ::NAME (e.g. "amazon.com") and reached through the magic accessors,
     * so results stay addressable by domain without dynamic properties.
     */
    private const VALIDATORS = [
        AmazonCom::class,
        CraigslistOrg::class,
        ExampleCom::class,
        GoogleCom::class,
        SsCom::class,
    ];

    /** @var array<string, AbstractRequestValidation> */
    private array $validators = [];

    public function __construct(?HttpClientInterface $client = null)
    {
        foreach (self::VALIDATORS as $validator) {
            $this->validators[$validator::NAME] = new $validator($client);
        }
    }

    /**
     * @return AbstractRequestValidation|null
     */
    public function __get(string $name): mixed
    {
        return $this->validators[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->validators[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->validators[$name]);
    }

    public function __unset(string $name): void
    {
        unset($this->validators[$name]);
    }

    /**
     * Serialise as "domain name => validation result".
     *
     * @return array<string, AbstractRequestValidation>
     */
    public function jsonSerialize(): array
    {
        return $this->validators;
    }
}

<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Validations\Domains\AbstractDomainValidation;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DomainsValidation implements \JsonSerializable
{
    use KeyedResultMap;

    /** @var array<string, AbstractDomainValidation> */
    private array $validators = [];

    /**
     * Domain validation is opt-in: no validators run unless the caller passes
     * them. Provide a list of validator classes (each extending
     * AbstractDomainValidation, see Domains\ExampleCom for the template). Each
     * is stored in $validators keyed by its ::NAME (e.g. "example.com") and
     * reached through the magic accessors, so results stay addressable by domain
     * without dynamic properties.
     *
     * @param array<class-string<AbstractDomainValidation>> $validators
     */
    public function __construct(?HttpClientInterface $client = null, array $validators = [])
    {
        foreach ($validators as $validatorClass) {
            $validator = new $validatorClass($client);
            $this->validators[$validator::NAME] = $validator;
        }
    }

    /**
     * @return array<string, AbstractDomainValidation>
     */
    protected function resultMap(): array
    {
        return $this->validators;
    }

    public function __set(string $name, mixed $value): void
    {
        if ($value instanceof AbstractDomainValidation) {
            $this->validators[$name] = $value;
        }
    }

    public function __unset(string $name): void
    {
        unset($this->validators[$name]);
    }

    /**
     * Serialise as "domain name => validation result".
     *
     * @return array<string, AbstractDomainValidation>
     */
    public function jsonSerialize(): array
    {
        return $this->validators;
    }
}

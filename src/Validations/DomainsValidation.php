<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Validations\Domains\AbstractDomainValidation;
use JsonSerializable;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DomainsValidation implements JsonSerializable, ValidationInterface
{
    use KeyedResultMap;

    /** @var array<string, AbstractDomainValidation> */
    private array $validators = [];

    private ?HttpClientInterface $client;

    /** @var array<class-string<AbstractDomainValidation>> */
    private array $validatorClasses = [];

    /**
     * Domain validation is opt-in: no validators run unless you configure them
     * via setValidators(). Construction performs no I/O.
     */
    public function __construct(?HttpClientInterface $client = null)
    {
        $this->client = $client;
    }

    public static function make(?HttpClientInterface $client = null): self
    {
        return new self($client);
    }

    /**
     * Register the domain validator classes to run (each extending
     * AbstractDomainValidation, see Domains\ExampleCom for the template). run()
     * instantiates and runs each, keyed by its ::NAME (e.g. "example.com") so
     * results stay addressable by domain. Set before run().
     *
     * @param array<class-string<AbstractDomainValidation>> $validators
     */
    public function setValidators(array $validators): self
    {
        $this->validatorClasses = $validators;

        return $this;
    }

    public function run(): self
    {
        $this->validators = [];

        foreach ($this->validatorClasses as $validatorClass) {
            $validator = $validatorClass::make($this->client)->run();
            $this->validators[$validator::NAME] = $validator;
        }

        return $this;
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

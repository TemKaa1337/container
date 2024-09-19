<?php

declare(strict_types=1);

namespace Temkaa\Container\Builder;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Provider\Config\ValidatorProvider;
use Temkaa\Container\Service\Compiler;
use Temkaa\Container\Validator\Config\ValidatorInterface;

/**
 * @psalm-api
 */
final class ContainerBuilder
{
    /**
     * @var Config[]
     */
    private array $configs = [];

    /**
     * @var ValidatorInterface[] $validators
     */
    private array $validators;

    /** @codeCoverageIgnore */
    public static function make(): self
    {
        return new self();
    }

    public function __construct()
    {
        $this->validators = (new ValidatorProvider())->provide();
    }

    public function add(Config $config): self
    {
        foreach ($this->validators as $validator) {
            $validator->validate($config);
        }

        $this->configs[] = $config;

        return $this;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function build(): ContainerInterface
    {
        return (new Compiler($this->configs))->compile();
    }
}

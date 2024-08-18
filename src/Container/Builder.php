<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Model\Container\ConfigNew;
use Temkaa\SimpleContainer\Provider\Config\ValidatorProvider;
use Temkaa\SimpleContainer\Validator\Config\ValidatorInterface;

/**
 * @psalm-api
 */
final class Builder
{
    /**
     * @var ConfigNew[]
     */
    private array $configs = [];

    /**
     * @var ValidatorInterface[] $validators
     */
    private array $validators;

    public function __construct()
    {
        $this->validators = (new ValidatorProvider())->provide();
    }

    public function add(ConfigNew $config): self
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
    public function compile(): ContainerInterface
    {
        return (new Compiler($this->configs))->compile();
    }
}

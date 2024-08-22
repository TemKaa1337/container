<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Builder;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Provider\Config\ValidatorProvider;
use Temkaa\SimpleContainer\Service\Compiler;
use Temkaa\SimpleContainer\Validator\Config\ValidatorInterface;

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

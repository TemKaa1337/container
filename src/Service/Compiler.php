<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Container;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Repository\DefinitionRepository;
use Temkaa\SimpleContainer\Service\Definition\BaseConfigurator;
use Temkaa\SimpleContainer\Service\Definition\Configurator\DecoratorConfigurator;
use Temkaa\SimpleContainer\Service\Definition\Configurator\InterfaceConfigurator;
use Temkaa\SimpleContainer\Service\Definition\Resolver;
use Temkaa\SimpleContainer\Validator\Definition\DuplicatedAliasValidator;

/**
 * @internal
 */
final readonly class Compiler
{
    /**
     * @param Config[] $configs
     */
    public function __construct(
        private array $configs,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function compile(): ContainerInterface
    {
        $configurator = new DecoratorConfigurator(new InterfaceConfigurator(new BaseConfigurator($this->configs)));
        $definitions = $configurator->configure();

        (new DuplicatedAliasValidator())->validate($definitions);

        $definitionResolver = new Resolver($definitions);
        $resolvedDefinitions = $definitionResolver->resolve();

        return new Container(new DefinitionRepository($resolvedDefinitions));
    }
}

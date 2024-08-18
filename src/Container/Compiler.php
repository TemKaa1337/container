<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Container;
use Temkaa\SimpleContainer\Definition\Builder;
use Temkaa\SimpleContainer\Definition\Resolver;
use Temkaa\SimpleContainer\Model\Container\ConfigNew;
use Temkaa\SimpleContainer\Repository\DefinitionRepository;
use Temkaa\SimpleContainer\Validator\DuplicatedDefinitionAliasValidator;

/**
 * @internal
 */
final readonly class Compiler
{
    /**
     * @param ConfigNew[] $configs
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
        $definitionBuilder = new Builder($this->configs);
        $definitions = $definitionBuilder->build();

        $definitionResolver = new Resolver($definitions);
        $resolvedDefinitions = $definitionResolver->resolve();

        (new DuplicatedDefinitionAliasValidator())->validate($resolvedDefinitions);

        $definitionRepository = new DefinitionRepository($resolvedDefinitions);

        return new Container($definitionRepository);
    }
}

<?php

declare(strict_types=1);

namespace Temkaa\Container;

use Psr\Container\ContainerInterface;
use ReflectionException;
use Temkaa\Container\Repository\DefinitionRepository;
use Temkaa\Container\Service\Definition\Instantiator;

/**
 * @api
 */
final readonly class Container implements ContainerInterface
{
    private Instantiator $instantiator;

    public function __construct(
        private DefinitionRepository $definitionRepository,
    ) {
        $this->instantiator = new Instantiator($this->definitionRepository);
    }

    /**
     * @psalm-suppress InvalidReturnStatement, InvalidReturnType, MoreSpecificImplementedParamType
     *
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @return T
     *
     * @throws ReflectionException
     */
    public function get(string $id): object
    {
        $definition = $this->definitionRepository->find($id);

        return $this->instantiator->instantiate($definition);
    }

    public function has(string $id): bool
    {
        return $this->definitionRepository->has($id);
    }
}

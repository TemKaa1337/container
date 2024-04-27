<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer;

use Psr\Container\ContainerInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Attribute\Autowire;
use Temkaa\SimpleContainer\Definition\Instantiator;
use Temkaa\SimpleContainer\Repository\DefinitionRepository;

// TODO: refactor
#[Autowire(load: false)]
final readonly class Container implements ContainerInterface
{
    public function __construct(
        private DefinitionRepository $definitionRepository,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function get(string $id): object
    {
        $definition = $this->definitionRepository->find($id);

        return $definition->isSingleton()
            ? $definition->getInstance()
            : (new Instantiator($this->definitionRepository))->instantiate($definition);
    }

    public function has(string $id): bool
    {
        return $this->definitionRepository->has($id);
    }
}

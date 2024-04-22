<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer;

use Psr\Container\ContainerInterface;
use Temkaa\SimpleContainer\Attribute\NonAutowirable;
use Temkaa\SimpleContainer\Repository\DefinitionRepository;

// TODO: add parameter repository?
#[NonAutowirable]
final readonly class Container implements ContainerInterface
{
    public function __construct(
        private DefinitionRepository $definitionRepository,
    ) {
    }

    public function get(string $id): object
    {
        return $this->definitionRepository->find($id)->getInstance();
    }

    public function has(string $id): bool
    {
        return $this->definitionRepository->has($id);
    }
}

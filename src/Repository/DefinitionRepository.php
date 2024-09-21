<?php

declare(strict_types=1);

namespace Temkaa\Container\Repository;

use Temkaa\Container\Attribute\Autowire;
use Temkaa\Container\Exception\EntryNotFoundException;
use Temkaa\Container\Model\Definition\Bag;
use Temkaa\Container\Model\Definition\ClassDefinition;
use Temkaa\Container\Model\Definition\DefinitionInterface;
use Temkaa\Container\Model\Definition\InterfaceDefinition;

/**
 * @internal
 */
#[Autowire(load: false)]
final readonly class DefinitionRepository
{
    public function __construct(
        private Bag $definitions,
    ) {
    }

    public function find(string $id): DefinitionInterface
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        if ($this->definitions->has($id)) {
            /** @psalm-suppress ArgumentTypeCoercion */
            return $this->resolveDecorators($this->definitions->get($id));
        }

        if ($entry = $this->findOneByAlias($id)) {
            return $this->resolveDecorators($entry);
        }

        throw new EntryNotFoundException(sprintf('Could not find entry "%s".', $id));
    }

    /**
     * @param class-string   $id
     * @param class-string[] $exclude
     *
     * @return DefinitionInterface[]
     */
    public function findAllByInstanceOf(string $id, array $exclude = []): array
    {
        $instanceOfDefinitions = [];
        foreach ($this->definitions as $definition) {
            if ($definition instanceof InterfaceDefinition) {
                continue;
            }

            /** @var ClassDefinition $definition */
            if (
                !in_array($definition->getId(), $exclude, strict: true)
                && (
                    in_array($id, $definition->getInstanceOf(), strict: true)
                    || in_array($id, $definition->getImplements(), strict: true)
                )
            ) {

                $instanceOfDefinitions[] = $definition;
            }
        }

        return $instanceOfDefinitions;
    }

    /**
     * @param string         $tag
     * @param class-string[] $exclude
     *
     * @return DefinitionInterface[]
     */
    public function findAllByTag(string $tag, array $exclude = []): array
    {
        $taggedDefinitions = [];
        foreach ($this->definitions as $definition) {
            if ($definition instanceof InterfaceDefinition) {
                continue;
            }

            /** @var ClassDefinition $definition */
            if (
                in_array($tag, $definition->getTags(), strict: true)
                && !in_array($definition->getId(), $exclude, strict: true)
            ) {
                $taggedDefinitions[] = $definition;
            }
        }

        return $taggedDefinitions;
    }

    public function has(string $id): bool
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return $this->definitions->has($id) || $this->findOneByAlias($id);
    }

    private function findOneByAlias(string $alias): ?DefinitionInterface
    {
        foreach ($this->definitions as $definition) {
            if ($definition instanceof InterfaceDefinition) {
                continue;
            }

            /** @var ClassDefinition $definition */
            if (in_array($alias, $definition->getAliases(), strict: true)) {
                return $definition;
            }
        }

        return null;
    }

    private function getRootDecoratorDefinition(DefinitionInterface $definition): DefinitionInterface
    {
        while ($definition->getDecoratedBy()) {
            /** @psalm-suppress PossiblyNullArgument */
            $definition = $this->definitions->get($definition->getDecoratedBy());
        }

        return $definition;
    }

    private function resolveDecorators(DefinitionInterface $definition): DefinitionInterface
    {
        if ($definition instanceof InterfaceDefinition) {
            if (!$definition->getDecoratedBy()) {
                return $this->definitions->get($definition->getImplementedById());
            }

            return $this->getRootDecoratorDefinition($definition);
        }

        /** @var ClassDefinition $definition */
        $decoratedBy = $definition->getDecoratedBy();
        $decorates = $definition->getDecorates();

        $isDecorationRoot = $decoratedBy && !$decorates;
        if (!$isDecorationRoot) {
            return $definition;
        }

        return $this->getRootDecoratorDefinition($definition);
    }
}

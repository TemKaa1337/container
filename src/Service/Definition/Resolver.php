<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Exception\CircularReferenceException;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Model\Definition\DefinitionInterface;
use Temkaa\SimpleContainer\Model\Definition\InterfaceDefinition;
use Temkaa\SimpleContainer\Model\Reference\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Model\Reference\Deferred\TaggedReference;
use Temkaa\SimpleContainer\Model\Reference\Reference;
use Temkaa\SimpleContainer\Model\Reference\ReferenceInterface;
use Temkaa\SimpleContainer\Repository\DefinitionRepository;
use Temkaa\SimpleContainer\Util\Flag;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @internal
 */
final readonly class Resolver
{
    private Instantiator $instantiator;

    /**
     * @param DefinitionInterface[] $definitions
     */
    public function __construct(
        private array $definitions,
    ) {
        $this->instantiator = new Instantiator(new DefinitionRepository(array_values($definitions)));
    }

    /**
     * @return DefinitionInterface[]
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function resolve(): array
    {
        foreach ($this->definitions as $definition) {
            $this->resolveDefinition($definition);
        }

        return $this->definitions;
    }

    private function getDefinition(DecoratorReference|Reference $reference): DefinitionInterface
    {
        $definition = $this->definitions[$reference->getId()];
        if (!$definition instanceof InterfaceDefinition) {
            return $definition;
        }

        if (!$definition->getDecoratedBy()) {
            return $this->definitions[$definition->getId()];
        }

        $definition = $this->definitions[$definition->getDecoratedBy()];
        while ($definition->getDecoratedBy()) {
            /** @psalm-suppress PossiblyNullArrayOffset */
            $definition = $this->definitions[$definition->getDecoratedBy()];
        }

        return $definition;
    }

    /**
     * @param mixed $argument
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function resolveArgument(mixed $argument): mixed
    {
        if (!$argument instanceof ReferenceInterface) {
            return $argument;
        }

        if ($argument instanceof TaggedReference) {
            return $this->resolveTaggedArgument($argument);
        }

        /** @var DecoratorReference|Reference $argument */
        $definition = $this->getDefinition($argument);

        $this->resolveDefinition($definition);

        return $this->instantiator->instantiate($definition);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function resolveDefinition(DefinitionInterface $definition): void
    {
        if ($definition instanceof InterfaceDefinition) {
            $this->resolveDefinition($this->definitions[$definition->getImplementedById()]);

            return;
        }

        /** @var ClassDefinition $definition */
        if ($definition->hasInstance()) {
            return;
        }

        if (Flag::isToggled($definition->getId(), group: 'resolver')) {
            throw new CircularReferenceException($definition->getId(), Flag::getToggled(group: 'resolver'));
        }

        Flag::toggle($definition->getId(), group: 'resolver');

        $resolvedArguments = array_map(
            fn (mixed $argument): mixed => $this->resolveArgument($argument),
            $definition->getArguments(),
        );

        $reflection = new ReflectionClass($definition->getId());

        $definition->setInstance($reflection->newInstanceArgs($resolvedArguments));

        Flag::untoggle($definition->getId(), group: 'resolver');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function resolveTaggedArgument(TaggedReference $argument): array
    {
        $definitionRepository = new DefinitionRepository(array_values($this->definitions));
        $taggedDefinitions = $definitionRepository->findAllByTag($argument->getTag());

        $resolvedArguments = [];
        foreach ($taggedDefinitions as $taggedDefinition) {
            $this->resolveDefinition($this->definitions[$taggedDefinition->getId()]);

            $resolvedArguments[] = $this->instantiator->instantiate($this->definitions[$taggedDefinition->getId()]);
        }

        return $resolvedArguments;
    }
}

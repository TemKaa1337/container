<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\Container\Exception\CircularReferenceException;
use Temkaa\Container\Model\Definition\Bag;
use Temkaa\Container\Model\Definition\ClassDefinition;
use Temkaa\Container\Model\Definition\DefinitionInterface;
use Temkaa\Container\Model\Definition\InterfaceDefinition;
use Temkaa\Container\Model\Reference\Deferred\DecoratorReference;
use Temkaa\Container\Model\Reference\Deferred\InstanceOfIteratorReference;
use Temkaa\Container\Model\Reference\Deferred\TaggedIteratorReference;
use Temkaa\Container\Model\Reference\Reference;
use Temkaa\Container\Model\Reference\ReferenceInterface;
use Temkaa\Container\Repository\DefinitionRepository;
use Temkaa\Container\Util\Flag;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @internal
 */
final readonly class Resolver
{
    private Instantiator $instantiator;

    public function __construct(
        private Bag $definitions,
    ) {
        $this->instantiator = new Instantiator(new DefinitionRepository($definitions));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function resolve(): void
    {
        foreach ($this->definitions as $definition) {
            $this->resolveDefinition($definition);
        }
    }

    private function getDefinition(DecoratorReference|Reference $reference): DefinitionInterface
    {
        $definition = $this->definitions->get($reference->getId());
        if (!$definition instanceof InterfaceDefinition) {
            return $definition;
        }

        if (!$definition->getDecoratedBy()) {
            return $this->definitions->get($definition->getId());
        }

        $definition = $this->definitions->get($definition->getDecoratedBy());
        while ($definition->getDecoratedBy()) {
            /** @psalm-suppress PossiblyNullArgument */
            $definition = $this->definitions->get($definition->getDecoratedBy());
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

        if ($argument instanceof TaggedIteratorReference) {
            return $this->resolveTaggedIteratorArgument($argument);
        }

        if ($argument instanceof InstanceOfIteratorReference) {
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
            $this->resolveDefinition($this->definitions->get($definition->getImplementedById()));

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

        if ($factory = $definition->getFactory()) {
            $resolvedArguments = array_map(
                fn (mixed $argument): mixed => $this->resolveArgument($argument),
                $factory->getMethod()->getArguments(),
            );

            if ($factory->getMethod()->isStatic()) {
                /**
                 * @psalm-suppress MixedMethodCall
                 *
                 * @var object $instance
                 */
                $instance = $factory->getId()::{$factory->getMethod()->getName()}(...$resolvedArguments);
            } else {
                /**
                 * @noinspection   PhpPossiblePolymorphicInvocationInspection
                 *
                 * @psalm-suppress UndefinedInterfaceMethod
                 *
                 * @var array $unresolvedFactoryClassArguments
                 */
                $unresolvedFactoryClassArguments = $this->definitions->get($factory->getId())->getArguments();
                $factoryInstanceResolvedArguments = array_map(
                    fn (mixed $argument): mixed => $this->resolveArgument($argument),
                    $unresolvedFactoryClassArguments,
                );

                $factoryId = $factory->getId();
                /** @psalm-suppress MixedMethodCall */
                $factoryInstance = new $factoryId(...$factoryInstanceResolvedArguments);

                /**
                 * @psalm-suppress MixedMethodCall, MixedAssignment
                 *
                 * @var object $instance
                 */
                $instance = $factoryInstance->{$factory->getMethod()->getName()}(...$resolvedArguments);
            }
        } else {
            $resolvedArguments = array_map(
                fn (mixed $argument): mixed => $this->resolveArgument($argument),
                $definition->getArguments(),
            );

            $reflection = new ReflectionClass($definition->getId());

            $instance = $reflection->newInstanceArgs($resolvedArguments);
        }

        foreach ($definition->getRequiredMethodCalls() as $methodName => $methodArguments) {
            $resolvedArguments = array_map(
                fn (mixed $argument): mixed => $this->resolveArgument($argument),
                $methodArguments,
            );

            /** @psalm-suppress MixedMethodCall */
            $instance->{$methodName}(...$resolvedArguments);
        }

        $definition->setInstance($instance);

        Flag::untoggle($definition->getId(), group: 'resolver');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function resolveTaggedArgument(InstanceOfIteratorReference $argument): array
    {
        $definitionRepository = new DefinitionRepository($this->definitions);
        $instanceOfDefinitions = $definitionRepository->findAllByInstanceOf(
            $argument->getId(),
            $argument->getExclude(),
        );

        $resolvedArguments = [];
        foreach ($instanceOfDefinitions as $instanceOfDefinition) {
            $this->resolveDefinition($this->definitions->get($instanceOfDefinition->getId()));

            $resolvedArguments[] = $this->instantiator->instantiate(
                $this->definitions->get($instanceOfDefinition->getId()),
            );
        }

        return $resolvedArguments;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function resolveTaggedIteratorArgument(TaggedIteratorReference $argument): array
    {
        $definitionRepository = new DefinitionRepository($this->definitions);
        $taggedDefinitions = $definitionRepository->findAllByTag($argument->getTag(), $argument->getExclude());

        $resolvedArguments = [];
        foreach ($taggedDefinitions as $taggedDefinition) {
            $this->resolveDefinition($this->definitions->get($taggedDefinition->getId()));

            $resolvedArguments[] = $this->instantiator->instantiate(
                $this->definitions->get($taggedDefinition->getId()),
            );
        }

        return $resolvedArguments;
    }
}

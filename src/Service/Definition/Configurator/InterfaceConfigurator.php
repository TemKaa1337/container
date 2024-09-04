<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition\Configurator;

use Temkaa\SimpleContainer\Exception\Config\EntryNotFoundException;
use Temkaa\SimpleContainer\Factory\Definition\ClassFactoryFactory;
use Temkaa\SimpleContainer\Factory\Definition\InterfaceFactory;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Model\Reference\Deferred\InterfaceReference;
use Temkaa\SimpleContainer\Model\Reference\Reference;
use Temkaa\SimpleContainer\Service\Definition\ConfiguratorInterface;

/**
 * @internal
 */
final readonly class InterfaceConfigurator implements ConfiguratorInterface
{
    public function __construct(
        private ConfiguratorInterface $configurator,
    ) {
    }

    public function configure(): Bag
    {
        $definitions = $this->configurator->configure();

        $this->addMissingInterfaceDefinitions($definitions);

        $this->updateInterfaceReferences($definitions);

        return $definitions;
    }

    private function addMissingInterfaceDefinitions(Bag $definitions): void
    {
        $interfaceImplementations = $this->collectInterfaceImplementations($definitions);

        /**
         * We auto bind interface to its implementations in 2 cases:
         * 1. there is only one interface implementation
         * 2. there are multiple interface implementations but only one which does not decorate any other class
         */
        foreach ($interfaceImplementations as $interface => $definitionIds) {
            if (count($definitionIds) === 1) {
                $definitions->add(
                    InterfaceFactory::create(
                        $interface,
                        implementedById: current($definitionIds),
                    ),
                );

                continue;
            }

            $interfaceImplementations = array_values(
                array_filter(
                    $definitionIds,
                    static function (string $definitionId) use ($definitions): bool {
                        /** @var ClassDefinition $definition */
                        $definition = $definitions->get($definitionId);

                        return $definition->getDecorates() === null;
                    },
                ),
            );

            if (count($interfaceImplementations) === 1) {
                $definitions->add(
                    InterfaceFactory::create(
                        $interface,
                        implementedById: current($interfaceImplementations),
                    ),
                );
            }
        }
    }

    /**
     * @param Bag $definitions
     *
     * @return array<class-string, class-string[]>
     */
    private function collectInterfaceImplementations(Bag $definitions): array
    {
        $interfaceImplementations = [];
        foreach ($definitions->all() as $definition) {
            if (!$definition instanceof ClassDefinition) {
                continue;
            }

            if (!$interfaces = $definition->getImplements()) {
                continue;
            }

            foreach ($interfaces as $interface) {
                $interfaceImplementations[$interface] ??= [];
                $interfaceImplementations[$interface][] = $definition->getId();
            }
        }

        return $interfaceImplementations;
    }

    /**
     * @return array<class-string, class-string[]>
     */
    private function collectUnboundInterfaces(Bag $definitions): array
    {
        $unboundInterfaces = [];
        foreach ($definitions as $definition) {
            if (!$definition instanceof ClassDefinition) {
                continue;
            }

            $unboundInterfaces = $this->getUnboundInterfacesFromArguments(
                $definition->getArguments(),
                $unboundInterfaces,
                $definition->getId(),
            );
            $unboundInterfaces = $this->getUnboundInterfacesFromArguments(
                $definition->getFactory()?->getMethod()?->getArguments() ?? [],
                $unboundInterfaces,
                $definition->getId(),
            );
            foreach ($definition->getRequiredMethodCalls() as $requiredMethodArguments) {
                $unboundInterfaces = $this->getUnboundInterfacesFromArguments(
                    $requiredMethodArguments,
                    $unboundInterfaces,
                    $definition->getId(),
                );
            }
        }

        return $unboundInterfaces;
    }

    /**
     * @param array                               $arguments
     * @param array<class-string, class-string[]> $unboundInterfaces
     * @param class-string                        $id
     *
     * @return array<class-string, class-string[]>
     */
    private function getUnboundInterfacesFromArguments(array $arguments, array $unboundInterfaces, string $id): array
    {
        foreach ($arguments as $argument) {
            if (!$argument instanceof InterfaceReference) {
                continue;
            }

            $unboundInterfaces[$id] ??= [];
            $unboundInterfaces[$id][] = $argument->getId();
        }

        return $unboundInterfaces;
    }

    /**
     * @param array        $arguments
     * @param class-string $interface
     *
     * @return array
     */
    private function updateArgumentInterfaceReferences(array $arguments, string $interface): array
    {
        /** @psalm-suppress MixedAssignment */
        foreach ($arguments as $index => $argument) {
            if (
                $argument instanceof InterfaceReference
                && $argument->getId() === $interface
            ) {
                $arguments[$index] = new Reference($interface);
            }
        }

        return $arguments;
    }

    private function updateInterfaceReferences(Bag $definitions): void
    {
        $unboundInterfaces = $this->collectUnboundInterfaces($definitions);

        foreach ($unboundInterfaces as $definitionId => $unboundInterfaceIds) {
            /** @var ClassDefinition $definition */
            $definition = $definitions->get($definitionId);
            $factory = $definition->getFactory();

            $resolvedDefinitionArguments = $definition->getArguments();
            $resolvedFactoryArguments = $factory?->getMethod()?->getArguments();

            foreach ($unboundInterfaceIds as $unboundInterfaceId) {
                if (!$definitions->has($unboundInterfaceId)) {
                    throw new EntryNotFoundException(
                        sprintf('Could not find interface implementation for "%s".', $unboundInterfaceId),
                    );
                }

                $resolvedDefinitionArguments = $this->updateArgumentInterfaceReferences(
                    $resolvedDefinitionArguments,
                    $unboundInterfaceId,
                );

                $resolvedFactoryArguments = $this->updateArgumentInterfaceReferences(
                    $resolvedFactoryArguments ?? [],
                    $unboundInterfaceId,
                );

                $requiredMethodCallsInfo = $definition->getRequiredMethodCalls();
                foreach ($requiredMethodCallsInfo as $method => $requiredMethodArguments) {
                    $requiredMethodCallsInfo[$method] = $this->updateArgumentInterfaceReferences(
                        $requiredMethodArguments,
                        $unboundInterfaceId,
                    );
                }

                $definition->setRequiredMethodCalls($requiredMethodCallsInfo);
            }

            $definition->setArguments($resolvedDefinitionArguments);

            /** @psalm-suppress RiskyTruthyFalsyComparison */
            if ($resolvedFactoryArguments && $factory) {
                $definition->setFactory(
                    ClassFactoryFactory::create(
                        $factory->getId(),
                        $factory->getMethod()->getName(),
                        $resolvedFactoryArguments,
                        $factory->getMethod()->isStatic(),
                    ),
                );
            }
        }
    }
}

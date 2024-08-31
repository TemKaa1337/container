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

    private function collectUnboundInterfaces(Bag $definitions): array
    {
        $unboundInterfaces = [];
        foreach ($definitions as $definition) {
            if (!$definition instanceof ClassDefinition) {
                continue;
            }

            /** @psalm-suppress MixedAssignment */
            foreach ($definition->getArguments() as $argument) {
                if (!$argument instanceof InterfaceReference) {
                    continue;
                }

                $unboundInterfaces[$definition->getId()] ??= [];
                $unboundInterfaces[$definition->getId()][] = $argument->getId();
            }

            /** @psalm-suppress MixedAssignment */
            foreach ($definition->getFactory()?->getMethod()?->getArguments() ?? [] as $argument) {
                if (!$argument instanceof InterfaceReference) {
                    continue;
                }

                $unboundInterfaces[$definition->getId()] ??= [];
                $unboundInterfaces[$definition->getId()][] = $argument->getId();
            }
        }

        return $unboundInterfaces;
    }

    private function updateInterfaceReferences(Bag $definitions): void
    {
        $unboundInterfaces = $this->collectUnboundInterfaces($definitions);

        foreach ($unboundInterfaces as $definitionId => $unboundInterfaceIds) {
            /** @var ClassDefinition $definition */
            $definition = $definitions->get($definitionId);
            $resolvedArguments = $definition->getArguments();
            $resolvedFactoryArguments = $definition->getFactory()?->getMethod()?->getArguments();

            foreach ($unboundInterfaceIds as $unboundInterfaceId) {
                if (!$definitions->has($unboundInterfaceId)) {
                    throw new EntryNotFoundException(
                        sprintf('Could not find interface implementation for "%s".', $unboundInterfaceId),
                    );
                }

                /** @psalm-suppress MixedAssignment */
                foreach ($resolvedArguments as $index => $resolvedArgument) {
                    if (
                        $resolvedArgument instanceof InterfaceReference
                        && $resolvedArgument->getId() === $unboundInterfaceId
                    ) {
                        $resolvedArguments[$index] = new Reference($unboundInterfaceId);
                    }
                }

                if ($resolvedFactoryArguments !== null) {
                    /** @psalm-suppress MixedAssignment */
                    foreach ($resolvedFactoryArguments as $index => $resolvedArgument) {
                        if (
                            $resolvedArgument instanceof InterfaceReference
                            && $resolvedArgument->getId() === $unboundInterfaceId
                        ) {
                            $resolvedFactoryArguments[$index] = new Reference($unboundInterfaceId);
                        }
                    }
                }
            }

            $definition->setArguments($resolvedArguments);

            if ($resolvedFactoryArguments !== null) {
                $factory = $definition->getFactory();

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

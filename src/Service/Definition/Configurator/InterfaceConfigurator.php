<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition\Configurator;

use Temkaa\SimpleContainer\Exception\Config\EntryNotFoundException;
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

        [$unboundInterfaces, $interfaceImplementations] = $this->collectInterfaces($definitions);

        $this->addInterfaceDefinitions($definitions, $interfaceImplementations);

        $this->updateInterfaceReferences($definitions, $unboundInterfaces);

        return $definitions;
    }

    /**
     * @param Bag                                 $definitions
     * @param array<class-string, class-string[]> $interfaceImplementations
     */
    private function addInterfaceDefinitions(Bag $definitions, array $interfaceImplementations): void
    {
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

            $interfaceDecorators = array_values(
                array_filter(
                    $definitionIds,
                    static function (string $definitionId) use ($definitions, $interface): bool {
                        /** @var ClassDefinition $definition */
                        $definition = $definitions->get($definitionId);

                        return $definition->getDecorates()?->getId() === $interface;
                    },
                ),
            );

            if (!$interfaceDecorators) {
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
     * @return array{
     *     0: array<class-string, class-string[]>,
     *     1: array<class-string, class-string[]>
     * }
     */
    private function collectInterfaces(Bag $definitions): array
    {
        $unboundInterfaces = [];
        $interfaceImplementations = [];
        foreach ($definitions->all() as $definition) {
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

            if (!$interfaces = $definition->getImplements()) {
                continue;
            }

            foreach ($interfaces as $interface) {
                $interfaceImplementations[$interface] ??= [];
                $interfaceImplementations[$interface][] = $definition->getId();
            }
        }

        return [$unboundInterfaces, $interfaceImplementations];
    }

    /**
     * @param Bag                                 $definitions
     * @param array<class-string, class-string[]> $unboundInterfaces
     *
     * @return void
     */
    private function updateInterfaceReferences(Bag $definitions, array $unboundInterfaces): void
    {
        foreach ($unboundInterfaces as $definitionId => $unboundInterfaceIds) {

            /** @var ClassDefinition $definition */
            $definition = $definitions->get($definitionId);
            $resolvedArguments = $definition->getArguments();

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
            }

            $definition->setArguments($resolvedArguments);
        }
    }
}

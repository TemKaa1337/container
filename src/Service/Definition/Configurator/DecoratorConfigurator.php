<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition\Configurator;

use Temkaa\SimpleContainer\Factory\Definition\ClassFactoryFactory;
use Temkaa\SimpleContainer\Factory\Definition\DecoratorFactory;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Model\Definition\DefinitionInterface;
use Temkaa\SimpleContainer\Model\Definition\InterfaceDefinition;
use Temkaa\SimpleContainer\Model\Reference\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Service\Definition\ConfiguratorInterface;

final readonly class DecoratorConfigurator implements ConfiguratorInterface
{
    public function __construct(
        private ConfiguratorInterface $configurator,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function configure(): Bag
    {
        $definitions = $this->configurator->configure();
        foreach ($this->getDecorators($definitions) as $decoratedId => $decorators) {
            $decorators = $this->sortByPriority($decorators);

            $rootDecoratedDefinition = $definitions->get($decoratedId);
            if ($decorators) {
                $rootDecoratedDefinition->setDecoratedBy(current($decorators)->getId());
            }

            $decoratorsCount = count($decorators);
            for ($i = 0; $i < $decoratorsCount; $i++) {
                $previousDecorator = $decorators[$i - 1] ?? null;
                $currentDecorator = $decorators[$i];
                $nextDecorator = $decorators[$i + 1] ?? null;

                $arguments = $this->updateDecoratorReferences(
                    $currentDecorator->getArguments(),
                    $decoratedId,
                    $definitions,
                    $previousDecorator,
                    $rootDecoratedDefinition,
                );

                $currentDecorator->setArguments($arguments);

                if ($factory = $currentDecorator->getFactory()) {
                    $arguments = $this->updateDecoratorReferences(
                        $factory->getMethod()->getArguments(),
                        $decoratedId,
                        $definitions,
                        $previousDecorator,
                        $rootDecoratedDefinition,
                    );

                    $currentDecorator->setFactory(
                        ClassFactoryFactory::create(
                            $factory->getId(),
                            $factory->getMethod()->getName(),
                            $arguments,
                            $factory->getMethod()->isStatic(),
                        ),
                    );
                }

                if ($previousDecorator && $decorates = $currentDecorator->getDecorates()) {
                    $currentDecorator->setDecorates(
                        DecoratorFactory::create(
                            $previousDecorator->getId(),
                            $decorates->getPriority(),
                            $decorates->getSignature(),
                        ),
                    );
                }

                if ($nextDecorator) {
                    $currentDecorator->setDecoratedBy($nextDecorator->getId());
                }
            }
        }

        return $definitions;
    }

    /**
     * @return array<class-string, ClassDefinition[]>
     */
    private function getDecorators(Bag $definitions): array
    {
        $decorators = [];

        $definitions = array_filter(
            $definitions->all(),
            static fn (DefinitionInterface $definition): bool => $definition instanceof ClassDefinition,
        );

        foreach ($definitions as $definition) {
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            if ($decorates = $definition->getDecorates()) {
                $decorators[$decorates->getId()] ??= [];
                $decorators[$decorates->getId()][] = $definition;
            }
        }

        return $decorators;
    }

    /**
     * @param ClassDefinition[] $definitions
     *
     * @return ClassDefinition[]
     */
    private function sortByPriority(array $definitions): array
    {
        usort(
            $definitions,
            static function (ClassDefinition $prev, ClassDefinition $current): int {
                /** @psalm-suppress PossiblyNullReference */
                $prevPriority = $prev->getDecorates()->getPriority();
                /** @psalm-suppress PossiblyNullReference */
                $currentPriority = $current->getDecorates()->getPriority();

                if ($prevPriority === $currentPriority) {
                    return 0;
                }

                return $prevPriority > $currentPriority ? -1 : 1;
            },
        );

        return $definitions;
    }

    private function updateDecoratorReferences(
        array $arguments,
        string $decoratedId,
        Bag $definitions,
        ?ClassDefinition $previousDecorator,
        DefinitionInterface $rootDecoratedDefinition,
    ): array {
        foreach ($arguments as $index => $argument) {
            if (!$argument instanceof DecoratorReference) {
                continue;
            }

            if ($previousDecorator && $argument->getId() === $decoratedId) {
                $arguments[$index] = new DecoratorReference(
                    $previousDecorator->getId(),
                    $argument->getPriority(),
                    $argument->getSignature(),
                );
            } else if (!$previousDecorator && $rootDecoratedDefinition instanceof InterfaceDefinition) {
                $arguments[$index] = new DecoratorReference(
                    $definitions->get($rootDecoratedDefinition->getImplementedById())->getId(),
                    $argument->getPriority(),
                    $argument->getSignature(),
                );
            }
        }

        return $arguments;
    }
}

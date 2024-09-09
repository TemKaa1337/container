<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\SimpleContainer\Exception\NonAutowirableClassException;
use Temkaa\SimpleContainer\Exception\UninstantiableEntryException;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Config\Decorator;
use Temkaa\SimpleContainer\Model\Config\Factory;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Reference\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Model\Reference\Reference;
use Temkaa\SimpleContainer\Service\Definition\Configurator\Argument\BoundVariableConfigurator;
use Temkaa\SimpleContainer\Service\Definition\Configurator\Argument\InstanceOfIteratorConfigurator;
use Temkaa\SimpleContainer\Service\Definition\Configurator\Argument\InterfaceConfigurator;
use Temkaa\SimpleContainer\Service\Definition\Configurator\Argument\TaggedIteratorConfigurator;
use Temkaa\SimpleContainer\Util\Flag;
use Temkaa\SimpleContainer\Validator\Definition\ArgumentValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @internal
 */
final readonly class ArgumentConfigurator
{
    private BoundVariableConfigurator $boundVariableConfigurator;

    private Configurator $definitionConfigurator;

    private InstanceOfIteratorConfigurator $instanceOfIteratorConfigurator;

    private InterfaceConfigurator $interfaceConfigurator;

    private TaggedIteratorConfigurator $taggedIteratorConfigurator;

    public function __construct(Configurator $definitionConfigurator)
    {
        $this->boundVariableConfigurator = new BoundVariableConfigurator();
        $this->definitionConfigurator = $definitionConfigurator;
        $this->instanceOfIteratorConfigurator = new InstanceOfIteratorConfigurator();
        $this->interfaceConfigurator = new InterfaceConfigurator($definitionConfigurator);
        $this->taggedIteratorConfigurator = new TaggedIteratorConfigurator();
    }

    /**
     * @param Config                $config
     * @param Bag                   $definitions
     * @param ReflectionParameter[] $arguments
     * @param class-string          $id
     * @param Factory|null          $factory
     * @param Decorator|null        $decorates
     *
     * @return array
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function configure(
        Config $config,
        Bag $definitions,
        array $arguments,
        string $id,
        ?Factory $factory,
        ?Decorator $decorates,
    ): array {
        (new ArgumentValidator())->validate($arguments, $id);

        return array_map(
            fn (mixed $argument): mixed => $this->configureArgument(
                $config,
                $definitions,
                $argument,
                $id,
                $factory,
                $decorates,
            ),
            $arguments,
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param Config              $config
     * @param Bag                 $definitions
     * @param ReflectionParameter $argument
     * @param class-string        $id
     * @param Factory|null        $factory
     * @param Decorator|null      $decorates
     *
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function configureArgument(
        Config $config,
        Bag $definitions,
        ReflectionParameter $argument,
        string $id,
        ?Factory $factory,
        ?Decorator $decorates,
    ): mixed {
        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();
        /** @var class-string $entryId */
        $entryId = $argumentType->getName();

        if ($decorates && $decorates->getId() === $entryId) {
            return new DecoratorReference($decorates->getId(), $decorates->getPriority());
        }

        if ($configuredArgument = $this->taggedIteratorConfigurator->configure($config, $argument, $id, $factory)) {
            return $configuredArgument;
        }

        if ($configuredArgument = $this->instanceOfIteratorConfigurator->configure($config, $argument, $id, $factory)) {
            return $configuredArgument;
        }

        [
            'value'    => $configuredArgument,
            'resolved' => $resolved,
        ] = $this->boundVariableConfigurator->configure($config, $argument, $id, $factory);

        if ($resolved) {
            return $configuredArgument;
        }

        if ($configuredArgument = $this->interfaceConfigurator->configure($config, $argument, $definitions, $entryId)) {
            return $configuredArgument;
        }

        if ($definitions->has($entryId)) {
            return new Reference($entryId);
        }

        try {
            $this->definitionConfigurator->configureDefinition($entryId);

            return new Reference($entryId);
        } catch (UninstantiableEntryException|NonAutowirableClassException $exception) {
            if (!$argument->isDefaultValueAvailable()) {
                throw $exception;
            }

            Flag::untoggle($entryId, group: 'definition');

            return $argument->getDefaultValue();
        }
    }
}

<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\Container\Exception\NonAutowirableClassException;
use Temkaa\Container\Exception\UninstantiableEntryException;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Config\Decorator;
use Temkaa\Container\Model\Config\Factory;
use Temkaa\Container\Model\Definition\Bag;
use Temkaa\Container\Model\Reference\Deferred\DecoratorReference;
use Temkaa\Container\Model\Reference\Reference;
use Temkaa\Container\Service\Definition\Configurator\Argument\BoundVariableConfigurator;
use Temkaa\Container\Service\Definition\Configurator\Argument\InstanceConfigurator;
use Temkaa\Container\Service\Definition\Configurator\Argument\InstanceOfIteratorConfigurator;
use Temkaa\Container\Service\Definition\Configurator\Argument\InterfaceConfigurator;
use Temkaa\Container\Service\Definition\Configurator\Argument\TaggedIteratorConfigurator;
use Temkaa\Container\Util\Flag;
use Temkaa\Container\Validator\Definition\ArgumentValidator;
use function array_map;
use function file_exists;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @internal
 */
final readonly class ArgumentConfigurator
{
    private BoundVariableConfigurator $boundVariableConfigurator;

    private Configurator $definitionConfigurator;

    private InstanceConfigurator $instanceConfigurator;

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
        $this->instanceConfigurator = new InstanceConfigurator();
    }

    /**
     * @param ReflectionParameter[] $arguments
     * @param class-string          $id
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
     * @param class-string $id
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

        if ($configuredArgument = $this->instanceConfigurator->configure($config, $argument, $id, $factory)) {
            return $configuredArgument;
        }

        if ($decorates && $decorates->getId() === $entryId) {
            return new DecoratorReference($decorates->getId(), $decorates->getPriority());
        }

        if ($configuredArgument = $this->taggedIteratorConfigurator->configure($config, $argument, $id, $factory)) {
            return $configuredArgument;
        }

        if ($configuredArgument = $this->instanceOfIteratorConfigurator->configure($config, $argument, $id, $factory)) {
            return $configuredArgument;
        }

        $value = $this->boundVariableConfigurator->configure($config, $argument, $id, $factory);
        if ($value->resolved) {
            return $value->value;
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

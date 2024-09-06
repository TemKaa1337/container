<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Config\Decorator;
use Temkaa\SimpleContainer\Model\Config\Factory;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Reference\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Model\Reference\Reference;
use Temkaa\SimpleContainer\Service\Definition\Configurator\Argument\InstanceOfIteratorConfigurator;
use Temkaa\SimpleContainer\Service\Definition\Configurator\Argument\InterfaceConfigurator;
use Temkaa\SimpleContainer\Service\Definition\Configurator\Argument\OtherConfigurator;
use Temkaa\SimpleContainer\Service\Definition\Configurator\Argument\TaggedIteratorConfigurator;
use Temkaa\SimpleContainer\Validator\Definition\Argument\DecoratorValidator;
use Temkaa\SimpleContainer\Validator\Definition\ArgumentValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @internal
 */
final readonly class ArgumentConfigurator
{
    private Configurator $definitionConfigurator;

    private InstanceOfIteratorConfigurator $instanceOfIteratorConfigurator;

    private InterfaceConfigurator $interfaceConfigurator;

    private OtherConfigurator $otherConfigurator;

    private TaggedIteratorConfigurator $taggedIteratorConfigurator;

    public function __construct(Configurator $definitionConfigurator)
    {
        $this->definitionConfigurator = $definitionConfigurator;
        $this->instanceOfIteratorConfigurator = new InstanceOfIteratorConfigurator();
        $this->interfaceConfigurator = new InterfaceConfigurator($definitionConfigurator);
        $this->otherConfigurator = new OtherConfigurator();
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
        (new DecoratorValidator())->validate($decorates, $arguments, $id);

        if ($decorates && count($arguments) === 1) {
            return [
                new DecoratorReference(
                    $decorates->getId(),
                    $decorates->getPriority(),
                    $decorates->getSignature(),
                ),
            ];
        }

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
        if ($decorates && $decorates->getSignature() === $argument->getName()) {
            return new DecoratorReference($decorates->getId(), $decorates->getPriority(), $decorates->getSignature());
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
        ] = $this->otherConfigurator->configure($config, $argument, $id, $factory);

        if ($resolved) {
            return $configuredArgument;
        }

        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();
        /** @var class-string $entryId */
        $entryId = $argumentType->getName();

        if ($configuredArgument = $this->interfaceConfigurator->configure($config, $definitions, $entryId)) {
            return $configuredArgument;
        }

        if (!$definitions->has($entryId)) {
            $this->definitionConfigurator->configureDefinition($entryId);
        }

        return new Reference($entryId);
    }
}

<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Definition\Configurator\Argument;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Temkaa\Container\Exception\ClassNotFoundException;
use Temkaa\Container\Factory\Definition\InterfaceFactory;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Definition\Bag;
use Temkaa\Container\Model\Reference\Deferred\InterfaceReference;
use Temkaa\Container\Model\Reference\Reference;
use Temkaa\Container\Model\Reference\ReferenceInterface;
use Temkaa\Container\Service\Definition\Configurator;

/**
 * @internal
 */
final readonly class InterfaceConfigurator
{
    public function __construct(
        private Configurator $definitionConfigurator,
    ) {
    }

    /**
     * @param Config              $config
     * @param ReflectionParameter $argument
     * @param Bag                 $definitions
     * @param class-string        $entryId
     *
     * @return ReferenceInterface|null
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function configure(
        Config $config,
        ReflectionParameter $argument,
        Bag $definitions,
        string $entryId,
    ): ?ReferenceInterface {
        try {
            $dependencyReflection = new ReflectionClass($entryId);
        } catch (ReflectionException) {
            throw new ClassNotFoundException($entryId);
        }

        if (!$dependencyReflection->isInterface()) {
            return null;
        }

        $interfaceName = $dependencyReflection->getName();
        if (!$config->hasBoundInterface($interfaceName)) {
            $hasDefaultValue = $argument->isDefaultValueAvailable();
            /** @var object|null $defaultValue */
            $defaultValue = $hasDefaultValue ? $argument->getDefaultValue() : null;

            return new InterfaceReference($interfaceName, $hasDefaultValue, $defaultValue);
        }

        $interfaceImplementation = $config->getBoundInterfaceImplementation($interfaceName);
        if (!$definitions->has($interfaceName)) {
            $definitions->add(
                InterfaceFactory::create(
                    $interfaceName,
                    $interfaceImplementation,
                ),
            );
        }

        $this->definitionConfigurator->configureDefinition($interfaceImplementation);

        return new Reference($interfaceName);
    }
}

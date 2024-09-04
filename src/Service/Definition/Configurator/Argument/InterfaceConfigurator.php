<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition\Configurator\Argument;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Factory\Definition\InterfaceFactory;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Reference\Deferred\InterfaceReference;
use Temkaa\SimpleContainer\Model\Reference\Reference;
use Temkaa\SimpleContainer\Model\Reference\ReferenceInterface;
use Temkaa\SimpleContainer\Service\Definition\Configurator;

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
     * @param Config       $config
     * @param Bag          $definitions
     * @param class-string $entryId
     *
     * @return ReferenceInterface|null
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function configure(Config $config, Bag $definitions, string $entryId): ?ReferenceInterface
    {
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
            return new InterfaceReference($interfaceName);
        }

        $interfaceImplementation = $config->getBoundInterfaceImplementation($interfaceName);
        $definitions->add(
            InterfaceFactory::create(
                $interfaceName,
                $interfaceImplementation,
            ),
        );

        $this->definitionConfigurator->configureDefinition($interfaceImplementation);

        return new Reference($interfaceName);
    }
}

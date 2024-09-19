<?php

declare(strict_types=1);

namespace Temkaa\Container\Model;

use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
use Temkaa\Container\Attribute\Bind\TaggedIterator;
use Temkaa\Container\Model\Config\ClassConfig;
use UnitEnum;

final readonly class Config
{
    /**
     * @param array<class-string, ClassConfig>                                 $boundedClasses
     * @param array<class-string, class-string>                                $boundedInterfaces
     * @param array<string, string|InstanceOfIterator|TaggedIterator|UnitEnum> $boundedVariables
     * @param string[]                                                         $excludedPaths
     * @param string[]                                                         $includedPaths
     */
    public function __construct(
        private array $boundedClasses,
        private array $boundedInterfaces,
        private array $boundedVariables,
        private array $excludedPaths,
        private array $includedPaths,
    ) {
    }

    /**
     * @param class-string $interface
     *
     * @return class-string
     */
    public function getBoundInterfaceImplementation(string $interface): string
    {
        return $this->boundedInterfaces[$interface];
    }

    /**
     * @param class-string $class
     *
     * @return ClassConfig|null
     */
    public function getBoundedClass(string $class): ?ClassConfig
    {
        return $this->boundedClasses[$class] ?? null;
    }

    /**
     * @return array<class-string, ClassConfig>
     */
    public function getBoundedClasses(): array
    {
        return $this->boundedClasses;
    }

    /**
     * A key in array is interface name and value is class name.
     *
     * @return array<class-string, class-string>
     */
    public function getBoundedInterfaces(): array
    {
        return $this->boundedInterfaces;
    }

    /**
     * @return array<string, string|InstanceOfIterator|TaggedIterator|UnitEnum>
     */
    public function getBoundedVariables(): array
    {
        return $this->boundedVariables;
    }

    /**
     * @return string[]
     */
    public function getExcludedPaths(): array
    {
        return $this->excludedPaths;
    }

    /**
     * @return string[]
     */
    public function getIncludedPaths(): array
    {
        return $this->includedPaths;
    }

    public function hasBoundInterface(string $interface): bool
    {
        return isset($this->boundedInterfaces[$interface]);
    }
}

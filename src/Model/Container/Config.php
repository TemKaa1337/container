<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Container;

use Psr\Container\ContainerExceptionInterface;
use Temkaa\SimpleContainer\Exception\EntryNotFoundException;

final class Config
{
    /**
     * @var array<class-string, array<string, string>>
     */
    private array $classBoundVariables = [];

    /**
     * @var array<class-string, bool>
     */
    private array $classSingletons = [];

    /**
     * @var array<class-string, string[]>
     */
    private array $classTags = [];

    /**
     * @var class-string[]
     */
    private array $excludedClasses = [];

    /**
     * @var array<string, string>
     */
    private array $globalBoundVariables = [];

    /**
     * @var class-string[]
     */
    private array $includedClasses = [];

    /**
     * @var array<class-string, class-string>
     */
    private array $interfaceImplementations = [];

    /**
     * @param class-string $class
     *
     * @return array<string, string>
     */
    public function getClassBoundVariables(string $class): array
    {
        return $this->classBoundVariables[$class] ?? [];
    }

    /**
     * @param array<class-string, array<string, string>> $classBoundVariables
     */
    public function setClassBoundVariables(array $classBoundVariables): Config
    {
        $this->classBoundVariables = $classBoundVariables;

        return $this;
    }

    /**
     * @param class-string $class
     *
     * @return bool
     */
    public function getClassSingleton(string $class): bool
    {
        return $this->classSingletons[$class];
    }

    /**
     * @param class-string $class
     *
     * @return string[]
     */
    public function getClassTags(string $class): array
    {
        return $this->classTags[$class] ?? [];
    }

    /**
     * @param array<class-string, string[]> $classTags
     */
    public function setClassTags(array $classTags): Config
    {
        $this->classTags = $classTags;

        return $this;
    }

    /**
     * @return class-string[]
     */
    public function getExcludedClasses(): array
    {
        return $this->excludedClasses;
    }

    /**
     * @param class-string[] $classes
     */
    public function setExcludedClasses(array $classes): Config
    {
        $this->excludedClasses = $classes;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getGlobalBoundVariables(): array
    {
        return $this->globalBoundVariables;
    }

    /**
     * @param array<string, string> $globalBoundVariables
     */
    public function setGlobalBoundVariables(array $globalBoundVariables): Config
    {
        $this->globalBoundVariables = $globalBoundVariables;

        return $this;
    }

    /**
     * @return class-string[]
     */
    public function getIncludedClasses(): array
    {
        return $this->includedClasses;
    }

    /**
     * @param class-string[] $classes
     */
    public function setIncludedClasses(array $classes): Config
    {
        $this->includedClasses = $classes;

        return $this;
    }

    /**
     * @param class-string $interface
     *
     * @return class-string
     * @throws ContainerExceptionInterface
     */
    public function getInterfaceImplementation(string $interface): string
    {
        if (!isset($this->interfaceImplementations[$interface])) {
            throw new EntryNotFoundException($interface);
        }

        return $this->interfaceImplementations[$interface];
    }

    /**
     * @param class-string $class
     *
     * @return bool
     */
    public function hasClassSingleton(string $class): bool
    {
        return isset($this->classSingletons[$class]);
    }

    /**
     * @param class-string $interface
     */
    public function hasImplementation(string $interface): bool
    {
        return isset($this->interfaceImplementations[$interface]);
    }

    /**
     * @param array<class-string, bool> $classSingletons
     */
    public function setClassSingletons(array $classSingletons): self
    {
        $this->classSingletons = $classSingletons;

        return $this;
    }

    /**
     * @param array<class-string, class-string> $interfaceImplementations
     */
    public function setInterfaceImplementations(array $interfaceImplementations): Config
    {
        $this->interfaceImplementations = $interfaceImplementations;

        return $this;
    }
}

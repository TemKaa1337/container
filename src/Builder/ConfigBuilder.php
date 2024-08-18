<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Builder;

// TODO: fix tests
// TODO: correct namespaces
// TODO: write some tests on decorates by alias
use Temkaa\SimpleContainer\Model\Container\ClassConfig;
use Temkaa\SimpleContainer\Model\Container\ConfigNew;

final class ConfigBuilder
{
    /**
     * @var array<class-string, ClassConfig>
     */
    private array $boundedClasses = [];

    /**
     * @var array<class-string, class-string>
     */
    private array $boundedInterfaces = [];

    /**
     * @var array<string, string>
     */
    private array $boundedVariables = [];

    /**
     * @var string[]
     */
    private array $exclude = [];

    /**
     * @var string[]
     */
    private array $include = [];

    public function bindClass(ClassConfig $class): self
    {
        $this->boundedClasses[$class->getClass()] = $class;

        return $this;
    }

    /**
     * @param class-string $interface
     * @param class-string $class
     */
    public function bindInterface(string $interface, string $class): self
    {
        $this->boundedInterfaces[$interface] = $class;

        return $this;
    }

    public function bindVariable(string $variableName, string $expression): self
    {
        $this->boundedVariables[str_replace('$', '', $variableName)] = $expression;

        return $this;
    }

    public function build(): ConfigNew
    {
        $includedPaths = array_diff($this->include, $this->exclude);

        return new ConfigNew(
            $this->boundedClasses,
            $this->boundedInterfaces,
            $this->boundedVariables,
            $this->exclude,
            $includedPaths,
        );
    }

    public function exclude(string $path): self
    {
        $this->exclude[] = $path;

        return $this;
    }

    public function include(string $path): self
    {
        $this->include[] = $path;

        return $this;
    }

    public static function make(): self
    {
        return new self();
    }
}

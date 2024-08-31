<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Config;

use UnitEnum;

final readonly class ClassConfig
{
    /**
     * @param class-string                   $class
     * @param string[]                       $aliases
     * @param array<string, string|UnitEnum> $boundVariables
     * @param Decorator|null                 $decorates
     * @param bool                           $singleton
     * @param string[]                       $tags
     * @param Factory|null                   $factory
     */
    public function __construct(
        private string $class,
        private array $aliases,
        private array $boundVariables,
        private ?Decorator $decorates,
        private bool $singleton,
        private array $tags,
        private ?Factory $factory,
    ) {
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @return array<string, string|UnitEnum>
     */
    public function getBoundedVariables(): array
    {
        return $this->boundVariables;
    }

    /**
     * @return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function getDecorates(): ?Decorator
    {
        return $this->decorates;
    }

    public function getFactory(): ?Factory
    {
        return $this->factory;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function isSingleton(): bool
    {
        return $this->singleton;
    }
}

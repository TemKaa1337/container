<?php

declare(strict_types=1);

namespace Temkaa\Container\Model\Definition;

use Temkaa\Container\Model\Config\Decorator;
use Temkaa\Container\Model\Definition\Class\Factory;
use function array_unique;
use function array_values;

/**
 * @psalm-suppress MissingConstructor
 *
 * @internal
 */
final class ClassDefinition implements DefinitionInterface
{
    public ?Factory $factory = null;

    /**
     * @var string[]
     */
    private array $aliases = [];

    private array $arguments = [];

    /**
     * @var class-string|null
     */
    private ?string $decoratedBy = null;

    private ?Decorator $decorates = null;

    /**
     * @var class-string $id
     */
    private string $id;

    /**
     * @var class-string[]
     */
    private array $implements = [];

    private object $instance;

    /**
     * @var class-string[]
     */
    private array $instanceOf = [];

    private bool $isSingleton = true;

    /**
     * @var array<string, array>
     */
    private array $requiredMethodCalls = [];

    /**
     * @var string[]
     */
    private array $tags = [];

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @param string[] $aliases
     */
    public function setAliases(array $aliases): self
    {
        $this->aliases = array_values(array_unique($aliases));

        return $this;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return class-string|null
     */
    public function getDecoratedBy(): ?string
    {
        return $this->decoratedBy;
    }

    /**
     * @param class-string $id
     */
    public function setDecoratedBy(string $id): self
    {
        $this->decoratedBy = $id;

        return $this;
    }

    public function getDecorates(): ?Decorator
    {
        return $this->decorates;
    }

    public function setDecorates(Decorator $decorator): self
    {
        $this->decorates = $decorator;

        return $this;
    }

    public function getFactory(): ?Factory
    {
        return $this->factory;
    }

    public function setFactory(Factory $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * @return class-string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param class-string $id
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return class-string[]
     */
    public function getImplements(): array
    {
        return $this->implements;
    }

    /**
     * @param class-string[] $interfaces
     */
    public function setImplements(array $interfaces): self
    {
        $this->implements = $interfaces;

        return $this;
    }

    public function getInstance(): object
    {
        return $this->instance;
    }

    public function setInstance(object $instance): self
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * @return class-string[]
     */
    public function getInstanceOf(): array
    {
        return $this->instanceOf;
    }

    /**
     * @param class-string[] $instanceOf
     */
    public function setInstanceOf(array $instanceOf): self
    {
        $this->instanceOf = $instanceOf;

        return $this;
    }

    /**
     * @return array<string, array>
     */
    public function getRequiredMethodCalls(): array
    {
        return $this->requiredMethodCalls;
    }

    /**
     * @param array<string, array> $methods
     */
    public function setRequiredMethodCalls(array $methods): self
    {
        $this->requiredMethodCalls = $methods;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param string[] $tags
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function hasInstance(): bool
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return (bool) ($this->instance ?? null);
    }

    public function isSingleton(): bool
    {
        return $this->isSingleton;
    }

    public function setIsSingleton(bool $isSingleton): ClassDefinition
    {
        $this->isSingleton = $isSingleton;

        return $this;
    }
}

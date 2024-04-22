<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model;

final class Definition
{
    /**
     * @var string[]
     */
    private array $aliases = [];

    private array $arguments = [];

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
     * @var string[]
     */
    private array $tags = [];

    public function addAlias(string $alias): self
    {
        if (!in_array($alias, $this->getAliases(), strict: true)) {
            $this->aliases[] = $alias;
        }

        return $this;
    }

    /**
     * @param string[] $aliases
     */
    public function addAliases(array $aliases): self
    {
        $aliases = [...$this->getAliases(), ...$aliases];

        $this->setAliases(array_values(array_unique($aliases)));

        return $this;
    }

    public function addArgument(mixed $value): self
    {
        $this->arguments[] = $value;

        return $this;
    }

    /**
     * @param class-string[] $interfaces
     */
    public function addImplements(array $interfaces): self
    {
        /** @var class-string[] $interfaces */
        $interfaces = [...$this->getImplements(), ...$interfaces];

        $this->setImplements(array_values(array_unique($interfaces)));

        return $this;
    }

    /**
     * @param string[] $tags
     */
    public function addTags(array $tags): self
    {
        $tags = [...$this->getTags(), ...$tags];

        $this->setTags(array_values(array_unique($tags)));

        return $this;
    }

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
        $this->aliases = $aliases;

        return $this;
    }

    public function getArguments(): array
    {
        return $this->arguments;
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
     * @return string[]
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
        return boolval($this->instance ?? null);
    }
}

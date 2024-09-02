<?php

declare(strict_types=1);

namespace Tests\Helper\Service;

/**
 * @psalm-suppress MissingConstructor
 */
final class ClassBuilder
{
    private string $absolutePath;

    private array $attributes = [];

    /**
     * @var string[]
     */
    private array $body = [];

    private array $constructorArguments = [];

    private string $constructorVisibility = 'public';

    private array $extends = [];

    private bool $hasConstructor = false;

    private array $interfaceImplementations = [];

    private string $name;

    private string $postfix = '';

    private string $prefix = 'final class';

    public function getAbsolutePath(): string
    {
        return $this->absolutePath;
    }

    public function setAbsolutePath(string $absolutePath): self
    {
        $this->absolutePath = $absolutePath;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @param string[] $body
     */
    public function setBody(array $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getConstructorArguments(): array
    {
        return $this->constructorArguments;
    }

    public function setConstructorArguments(array $constructorArguments): self
    {
        $this->constructorArguments = $constructorArguments;

        return $this;
    }

    public function getConstructorVisibility(): string
    {
        return $this->constructorVisibility;
    }

    public function setConstructorVisibility(string $constructorVisibility): self
    {
        $this->constructorVisibility = $constructorVisibility;

        return $this;
    }

    public function getExtends(): array
    {
        return $this->extends;
    }

    public function setExtends(array $extends): self
    {
        $this->extends = $extends;

        return $this;
    }

    public function getInterfaceImplementations(): array
    {
        return $this->interfaceImplementations;
    }

    public function setInterfaceImplementations(array $interfaceImplementations): self
    {
        $this->interfaceImplementations = $interfaceImplementations;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPostfix(): string
    {
        return $this->postfix;
    }

    public function setPostfix(string $postfix): self
    {
        $this->postfix = $postfix;

        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function hasConstructor(): bool
    {
        return $this->hasConstructor;
    }

    public function setHasConstructor(bool $hasConstructor): self
    {
        $this->hasConstructor = $hasConstructor;

        return $this;
    }
}

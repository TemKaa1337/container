<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model;

/**
 * @internal
 */
final class InterfaceDefinition implements DefinitionInterface
{
    /**
     * @var class-string|null
     */
    private ?string $decoratedBy = null;

    /**
     * @var class-string
     */
    private string $id;

    /**
     * @var class-string
     */
    private string $implementedById;

    public function getDecoratedBy(): ?string
    {
        return $this->decoratedBy;
    }

    public function setDecoratedBy(string $id): self
    {
        $this->decoratedBy = $id;

        return $this;
    }

    /**
     * @return  class-string
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
     * @return  class-string
     */
    public function getImplementedById(): string
    {
        return $this->implementedById;
    }

    /**
     * @param class-string $implementedById
     */
    public function setImplementedById(string $implementedById): self
    {
        $this->implementedById = $implementedById;

        return $this;
    }
}

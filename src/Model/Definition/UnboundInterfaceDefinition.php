<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Definition;

final class UnboundInterfaceDefinition implements DefinitionInterface
{
    /**
     * @var class-string
     */
    private string $id;

    /**
     * @param class-string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getDecoratedBy(): ?string
    {
        return null;
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
    public function setId(string $id): DefinitionInterface
    {
        $this->id = $id;

        return $this;
    }

    public function setDecoratedBy(string $id): DefinitionInterface
    {
        return $this;
    }
}

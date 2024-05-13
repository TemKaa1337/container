<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Definition;

/**
 * @internal
 */
final class Decorator
{
    /**
     * @var class-string
     */
    private string $id;

    private int $priority;

    private string $signature;

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

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function setSignature(string $signature): self
    {
        $this->signature = $signature;

        return $this;
    }
}

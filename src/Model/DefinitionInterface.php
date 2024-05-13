<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model;

/**
 * @internal
 */
interface DefinitionInterface
{
    /**
     * @return class-string|null
     */
    public function getDecoratedBy(): ?string;

    /**
     * @return class-string
     */
    public function getId(): string;

    /**
     * @psalm-suppress PossiblyUnusedReturnValue
     *
     * @param class-string $id
     */
    public function setDecoratedBy(string $id): self;

    /**
     * @param class-string $id
     */
    public function setId(string $id): self;
}

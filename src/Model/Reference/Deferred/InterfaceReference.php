<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Reference\Deferred;

use Temkaa\SimpleContainer\Model\Reference\ReferenceInterface;

/**
 * @internal
 */
final readonly class InterfaceReference implements ReferenceInterface
{
    /**
     * @param class-string $id
     */
    public function __construct(
        private string $id,
        private bool $hasDefaultValue,
        private ?object $defaultValue,
    ) {
    }

    public function getDefaultValue(): ?object
    {
        return $this->defaultValue;
    }

    /**
     * @return class-string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }
}

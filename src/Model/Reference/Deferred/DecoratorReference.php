<?php

declare(strict_types=1);

namespace Temkaa\Container\Model\Reference\Deferred;

use Temkaa\Container\Model\Reference\ReferenceInterface;

/**
 * @internal
 */
final readonly class DecoratorReference implements ReferenceInterface
{
    /**
     * @param class-string $id
     */
    public function __construct(
        private string $id,
        private int $priority,
    ) {
    }

    /**
     * @return class-string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}

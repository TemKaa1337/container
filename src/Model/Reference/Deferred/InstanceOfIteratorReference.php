<?php

declare(strict_types=1);

namespace Temkaa\Container\Model\Reference\Deferred;

use Temkaa\Container\Model\Reference\ReferenceInterface;

/**
 * @internal
 */
final readonly class InstanceOfIteratorReference implements ReferenceInterface
{
    /**
     * @param class-string   $id
     * @param class-string[] $exclude
     */
    public function __construct(
        private string $id,
        private array $exclude,
    ) {
    }

    /**
     * @return class-string[]
     */
    public function getExclude(): array
    {
        return $this->exclude;
    }

    /**
     * @return class-string
     */
    public function getId(): string
    {
        return $this->id;
    }
}

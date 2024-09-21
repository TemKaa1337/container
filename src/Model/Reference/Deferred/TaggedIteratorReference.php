<?php

declare(strict_types=1);

namespace Temkaa\Container\Model\Reference\Deferred;

use Temkaa\Container\Model\Reference\ReferenceInterface;

/**
 * @internal
 */
final readonly class TaggedIteratorReference implements ReferenceInterface
{
    /**
     * @param string         $tag
     * @param class-string[] $exclude
     */
    public function __construct(
        private string $tag,
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

    public function getTag(): string
    {
        return $this->tag;
    }
}

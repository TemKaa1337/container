<?php

declare(strict_types=1);

namespace Temkaa\Container\Model\Reference\Deferred;

use Temkaa\Container\Model\Reference\ReferenceInterface;

/**
 * @internal
 */
final readonly class TaggedIteratorReference implements ReferenceInterface
{
    public function __construct(
        private string $tag,
    ) {
    }

    public function getTag(): string
    {
        return $this->tag;
    }
}

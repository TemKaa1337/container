<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Reference\Deferred;

use Temkaa\SimpleContainer\Model\Reference\ReferenceInterface;

/**
 * @internal
 */
final readonly class TaggedReference implements ReferenceInterface
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

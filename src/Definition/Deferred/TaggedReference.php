<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Definition\Deferred;

use Temkaa\SimpleContainer\Definition\ReferenceInterface;

final readonly class TaggedReference implements ReferenceInterface
{
    public function __construct(
        public string $tag,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Temkaa\Container\Attribute\Bind;

use Attribute;

/**
 * @psalm-api
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class TaggedIterator
{
    public function __construct(
        public string $tag,
    ) {
    }
}

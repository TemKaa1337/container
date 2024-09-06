<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Attribute\Bind;

use Attribute;

/**
 * @psalm-api
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class InstanceOfIterator
{
    /**
     * @param class-string $id
     */
    public function __construct(
        public string $id,
    ) {
    }
}

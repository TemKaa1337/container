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
    public function __construct(
        public string $id,
    ) {
    }
}

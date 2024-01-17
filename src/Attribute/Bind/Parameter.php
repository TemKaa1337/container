<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Attribute\Bind;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Parameter
{
    public function __construct(
        public string $expression,
    ) {
    }
}

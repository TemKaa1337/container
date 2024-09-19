<?php

declare(strict_types=1);

namespace Example\AttributeInstanceOfIterator;

use Temkaa\Container\Attribute\Bind\InstanceOfIterator;

final class Class4
{
    public function __construct(
        #[InstanceOfIterator(Class3::class)]
        private readonly array $classes,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Example\AttributeTaggedIterator;

use Temkaa\SimpleContainer\Attribute\Bind\Parameter;

final readonly class Collector
{
    public function __construct(
        #[Parameter('!tagged tag')]
        public array $objects,
    ) {
    }
}

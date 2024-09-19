<?php

declare(strict_types=1);

namespace Example\AttributeTaggedIterator;

use Temkaa\Container\Attribute\Bind\TaggedIterator;

final readonly class Collector
{
    public function __construct(
        #[TaggedIterator('tag')]
        public array $objects,
    ) {
    }
}

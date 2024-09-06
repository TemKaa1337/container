<?php

declare(strict_types=1);

namespace Example\AttributeTaggedIterator;

use Temkaa\SimpleContainer\Attribute\Bind\TaggedIterator;

final readonly class InterfaceCollector
{
    public function __construct(
        #[TaggedIterator('interface_tag')]
        public array $objects,
    ) {
    }
}

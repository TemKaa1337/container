<?php

declare(strict_types=1);

namespace Example\AttributeTaggedIterator;

use Temkaa\SimpleContainer\Attribute\Bind\Tagged;

final readonly class InterfaceCollector
{
    public function __construct(
        #[Tagged('interface_tag')]
        public array $objects,
    ) {
    }
}

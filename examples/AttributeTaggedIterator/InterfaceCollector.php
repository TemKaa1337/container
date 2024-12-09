<?php

declare(strict_types=1);

namespace Example\AttributeTaggedIterator;

use Temkaa\Container\Attribute\Bind\TaggedIterator;
use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;

final readonly class InterfaceCollector
{
    public function __construct(
        #[TaggedIterator('interface_tag')]
        public array $list,
        #[TaggedIterator('interface_tag', format: IteratorFormat::ArrayWithClassNamespaceKey)]
        public array $arrayWithClassNamespaceKey,
        #[TaggedIterator('interface_tag', format: IteratorFormat::ArrayWithClassNameKey)]
        public array $arrayWithClassNameKey,
        #[TaggedIterator(
            'interface_tag',
            format: IteratorFormat::ArrayWithCustomKey,
            customFormatMapping: [
                Class3::class => 'third_class',
                Class4::class => 'fourth_class',
            ]
        )]
        public array $arrayWithCustomKey,
    ) {
    }
}

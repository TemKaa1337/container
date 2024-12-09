<?php

declare(strict_types=1);

namespace Example\AttributeTaggedIterator;

use Temkaa\Container\Attribute\Bind\TaggedIterator;
use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;

final readonly class Collector
{
    public function __construct(
        #[TaggedIterator('tag')]
        public array $list,
        #[TaggedIterator('tag', format: IteratorFormat::ArrayWithClassNamespaceKey)]
        public array $arrayWithClassNamespaceKey,
        #[TaggedIterator('tag', format: IteratorFormat::ArrayWithClassNameKey)]
        public array $arrayWithClassNameKey,
        #[TaggedIterator(
            'tag',
            format: IteratorFormat::ArrayWithCustomKey,
            customFormatMapping: [
                Class1::class => 'first_class',
                Class2::class => 'second_class',
            ]
        )]
        public array $arrayWithCustomKey,
    ) {
    }
}

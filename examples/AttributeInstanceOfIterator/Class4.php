<?php

declare(strict_types=1);

namespace Example\AttributeInstanceOfIterator;

use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;

final class Class4
{
    public function __construct(
        #[InstanceOfIterator(Class3::class)]
        private readonly array $list,
        #[InstanceOfIterator(Class3::class, format: IteratorFormat::ArrayWithClassNamespaceKey)]
        private readonly array $arrayWithClassNamespaceKey,
        #[InstanceOfIterator(Class3::class, format: IteratorFormat::ArrayWithClassNameKey)]
        private readonly array $arrayWithClassNameKey,
        #[InstanceOfIterator(
            Class3::class,
            format: IteratorFormat::ArrayWithCustomKey,
            customFormatMapping: [
                Class1::class => 'first_class',
                Class2::class => 'second_class',
            ]
        )]
        private readonly array $arrayWithCustomKey,
    ) {
    }
}

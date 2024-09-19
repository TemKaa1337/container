<?php

declare(strict_types=1);

namespace Example\AttributeFactory;

use Temkaa\Container\Attribute\Factory;

#[Factory(id: Class2::class, method: 'create')]
final readonly class Class1
{
    public function __construct(
        private Class3 $class3,
        private string $stringVar,
        private int $intVar,
        private array $tagged,
    ) {
    }
}

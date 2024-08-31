<?php

declare(strict_types=1);

namespace Example\ConfigFactory;

final readonly class Class2
{
    public function __construct(
        private Class3 $class,
        private string $stringVar,
    ) {
    }

    public function create(int $intVar, array $tagged): Class1
    {
        return new Class1($this->class, $this->stringVar, $intVar, $tagged);
    }
}

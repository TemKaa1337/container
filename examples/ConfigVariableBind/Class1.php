<?php

declare(strict_types=1);

namespace Example\ConfigVariableBind;

final readonly class Class1
{
    public function __construct(
        public string $variable1,
        public int $variable2,
    ) {
    }
}

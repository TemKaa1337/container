<?php

declare(strict_types=1);

namespace Example\ConfigVariableBind;

use Closure;

final readonly class Class1
{
    public function __construct(
        public string $stringVariable1,
        public string $stringVariable2,
        public string $stringVariable3,
        public string $stringVariable4,
        public int $intVariable1,
        public int $intVariable2,
        public float $floatVariable1,
        public float $floatVariable2,
        public bool $boolVariable1,
        public bool $boolVariable2,
        public bool $boolVariable3,
        public bool $boolVariable4,
        public bool $boolVariable5,
        public bool $boolVariable6,
        public Closure $closureVariable1,
        public object $objectVariable1,
    ) {
    }
}

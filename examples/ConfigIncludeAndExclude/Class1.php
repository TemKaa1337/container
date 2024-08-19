<?php

declare(strict_types=1);

namespace Example\ConfigIncludeAndExclude;

final readonly class Class1
{
    public function __construct(
        public Class2 $class2,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Example\AttributeInstance;

use Temkaa\Container\Attribute\Bind\Instance;

final readonly class Collector
{
    public function __construct(
        #[Instance(id: Class1::class)]
        public Interface1 $object,
    ) {
    }
}

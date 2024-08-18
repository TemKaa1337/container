<?php

declare(strict_types=1);

namespace Example\Example8;

use Temkaa\SimpleContainer\Attribute\Bind\Parameter;

final readonly class Collector
{
    public function __construct(
        #[Parameter('!tagged tag')]
        public array $objects,
    ) {
    }
}

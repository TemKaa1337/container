<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Definition;

final readonly class Reference implements ReferenceInterface
{
    public function __construct(
        public string $id,
    ) {
    }
}

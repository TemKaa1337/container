<?php

declare(strict_types=1);

namespace Example\ConfigInstanceOfIterator;

final class Class4
{
    public function __construct(
        private readonly array $list,
        private readonly array $arrayWithNamespaceKey,
        private readonly array $arrayWithClassNameKey,
        private readonly array $arrayWithCustomKey,
    ) {
    }
}

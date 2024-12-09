<?php

declare(strict_types=1);

namespace Example\ConfigTaggedIterator;

final readonly class Collector
{
    public function __construct(
        private array $list,
        private array $arrayWithNamespaceKey,
        private array $arrayWithClassNameKey,
        private array $arrayWithCustomKey,
    ) {
    }
}

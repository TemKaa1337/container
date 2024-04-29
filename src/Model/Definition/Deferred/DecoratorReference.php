<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Definition\Deferred;

use Temkaa\SimpleContainer\Model\Definition\ReferenceInterface;

final readonly class DecoratorReference implements ReferenceInterface
{
    public function __construct(
        public string $id,
        public int $priority,
        public string $signature,
    ) {
    }
}

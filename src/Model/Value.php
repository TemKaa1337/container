<?php

declare(strict_types=1);

namespace Temkaa\Container\Model;

/**
 * @internal
 */
final readonly class Value
{
    public function __construct(
        public mixed $value,
        public bool $resolved,
    ) {
    }
}

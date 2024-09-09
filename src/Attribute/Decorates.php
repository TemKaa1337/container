<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Attribute;

use Attribute;
use Temkaa\SimpleContainer\Model\Config\Decorator;

/**
 * @psalm-api
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Decorates
{
    /**
     * @param class-string $id
     */
    public function __construct(
        public string $id,
        public int $priority = Decorator::DEFAULT_PRIORITY,
    ) {
    }
}

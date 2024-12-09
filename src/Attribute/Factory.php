<?php

declare(strict_types=1);

namespace Temkaa\Container\Attribute;

use Attribute;

/**
 * @api
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Factory
{
    /**
     * @param class-string $id
     */
    public function __construct(
        public string $id,
        public string $method,
    ) {
    }
}

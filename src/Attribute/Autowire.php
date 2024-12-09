<?php

declare(strict_types=1);

namespace Temkaa\Container\Attribute;

use Attribute;

/**
 * @api
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Autowire
{
    public function __construct(
        public bool $load = true,
        public bool $singleton = true,
    ) {
    }
}

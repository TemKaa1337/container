<?php

declare(strict_types=1);

namespace Temkaa\Container\Attribute;

use Attribute;

/**
 * @psalm-api
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Tag
{
    public function __construct(
        public string $name,
    ) {
    }
}

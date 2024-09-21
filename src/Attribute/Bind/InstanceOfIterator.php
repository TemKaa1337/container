<?php

declare(strict_types=1);

namespace Temkaa\Container\Attribute\Bind;

use Attribute;

/**
 * @psalm-api
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class InstanceOfIterator
{
    /**
     * @param class-string   $id
     * @param class-string[] $exclude
     */
    public function __construct(
        public string $id,
        public array $exclude = [],
    ) {
    }
}

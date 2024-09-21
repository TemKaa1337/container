<?php

declare(strict_types=1);

namespace Temkaa\Container\Attribute\Bind;

use Attribute;

/**
 * @psalm-api
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class TaggedIterator
{
    /**
     * @param string         $tag
     * @param class-string[] $exclude
     */
    public function __construct(
        public string $tag,
        public array $exclude = [],
    ) {
    }
}

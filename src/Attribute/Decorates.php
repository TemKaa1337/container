<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Attribute;

use Attribute;

/**
 * @psalm-api
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Decorates
{
    public const DEFAULT_PRIORITY = 0;
    public const DEFAULT_SIGNATURE = 'inner';

    /**
     * @param class-string $id
     */
    public function __construct(
        public string $id,
        public int $priority = self::DEFAULT_PRIORITY,
        public string $signature = self::DEFAULT_SIGNATURE,
    ) {
    }
}

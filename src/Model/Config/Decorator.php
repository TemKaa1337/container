<?php

declare(strict_types=1);

namespace Temkaa\Container\Model\Config;

/**
 * @api
 */
final readonly class Decorator
{
    public const int DEFAULT_PRIORITY = 0;

    /**
     * @param class-string $id
     */
    public function __construct(
        private string $id,
        private int $priority = self::DEFAULT_PRIORITY,
    ) {
    }

    /**
     * @return class-string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}

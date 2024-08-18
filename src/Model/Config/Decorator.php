<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Config;

/**
 * @internal
 */
final readonly class Decorator
{
    public const int DEFAULT_PRIORITY = 0;
    public const string DEFAULT_SIGNATURE = 'inner';

    /**
     * @param class-string $id
     */
    public function __construct(
        private string $id,
        private int $priority = self::DEFAULT_PRIORITY,
        private string $signature = self::DEFAULT_SIGNATURE,
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

    public function getSignature(): string
    {
        return $this->signature;
    }
}

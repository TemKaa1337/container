<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Config;

final readonly class Decorator
{
    public const DEFAULT_PRIORITY = 0;
    public const DEFAULT_SIGNATURE = 'inner';

    /**
     * @param class-string $id
     */
    public function __construct(
        private string $id,
        private int $priority = self::DEFAULT_PRIORITY,
        private string $signature = self::DEFAULT_SIGNATURE,
    ) {
    }

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

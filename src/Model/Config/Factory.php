<?php

declare(strict_types=1);

namespace Temkaa\Container\Model\Config;

/**
 * @api
 */
final readonly class Factory
{
    /**
     * @param class-string         $id
     * @param array<string, mixed> $boundedVariables
     */
    public function __construct(
        private string $id,
        private string $method,
        private array $boundedVariables,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getBoundedVariables(): array
    {
        return $this->boundedVariables;
    }

    /**
     * @return class-string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}

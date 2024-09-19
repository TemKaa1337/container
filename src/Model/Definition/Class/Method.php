<?php

declare(strict_types=1);

namespace Temkaa\Container\Model\Definition\Class;

/**
 * @internal
 */
final readonly class Method
{
    public function __construct(
        private string $name,
        private array $arguments,
        private bool $isStatic,
    ) {
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }
}

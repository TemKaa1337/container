<?php

declare(strict_types=1);

namespace Temkaa\Container\Model\Definition\Class;

/**
 * @internal
 */
final readonly class Factory
{
    /**
     * @param class-string $id
     */
    public function __construct(
        private string $id,
        private Method $method,
    ) {
    }

    /**
     * @return class-string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getMethod(): Method
    {
        return $this->method;
    }
}

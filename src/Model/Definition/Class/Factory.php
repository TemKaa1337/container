<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Definition\Class;

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

    public function getId(): string
    {
        return $this->id;
    }

    public function getMethod(): Method
    {
        return $this->method;
    }
}

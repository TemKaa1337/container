<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Config;

use Temkaa\SimpleContainer\Attribute\Bind\Tagged;
use UnitEnum;

/**
 * @internal
 */
final readonly class Factory
{
    /**
     * @param class-string                          $id
     * @param string                                $method
     * @param array<string, string|Tagged|UnitEnum> $boundedVariables
     */
    public function __construct(
        private string $id,
        private string $method,
        private array $boundedVariables,
    ) {
    }

    /**
     * @return array<string, string|Tagged|UnitEnum>
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

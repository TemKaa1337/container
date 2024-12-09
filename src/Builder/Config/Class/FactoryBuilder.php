<?php

declare(strict_types=1);

namespace Temkaa\Container\Builder\Config\Class;

use Temkaa\Container\Factory\Config\ClassFactoryFactory;
use Temkaa\Container\Model\Config\Factory;
use function str_replace;

/**
 * @api
 */
final class FactoryBuilder
{
    /**
     * @var array<string, mixed>
     */
    private array $boundVariables = [];

    /**
     * @param class-string $id
     */
    public static function make(string $id, string $method): self
    {
        return new self($id, $method);
    }

    /**
     * @param class-string $id
     */
    public function __construct(
        private readonly string $id,
        private readonly string $method,
    ) {
    }

    public function bindVariable(string $name, mixed $value): self
    {
        $this->boundVariables[str_replace('$', '', $name)] = $value;

        return $this;
    }

    public function build(): Factory
    {
        return ClassFactoryFactory::create($this->id, $this->method, $this->boundVariables);
    }
}

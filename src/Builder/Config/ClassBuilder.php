<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Builder\Config;

use Temkaa\SimpleContainer\Attribute\Bind\Tagged;
use Temkaa\SimpleContainer\Factory\Definition\DecoratorFactory;
use Temkaa\SimpleContainer\Model\Config\ClassConfig;
use Temkaa\SimpleContainer\Model\Config\Decorator;
use Temkaa\SimpleContainer\Model\Config\Factory;
use UnitEnum;

/**
 * @psalm-api
 */
final class ClassBuilder
{
    /**
     * @var string[]
     */
    private array $aliases = [];

    /**
     * @var array<string, string|Tagged|UnitEnum>
     */
    private array $boundedVariables = [];

    private ?Decorator $decorates = null;

    private ?Factory $factory = null;

    /**
     * @var string[]
     */
    private array $methodCalls = [];

    private bool $singleton = true;

    /**
     * @var string[]
     */
    private array $tags = [];

    /**
     * @param class-string $class
     */
    public static function make(string $class): self
    {
        return new self($class);
    }

    /**
     * @param class-string $class
     */
    public function __construct(
        private readonly string $class,
    ) {
    }

    public function alias(string $alias): self
    {
        $this->aliases[] = $alias;

        return $this;
    }

    public function bindVariable(string $variableName, string|Tagged|UnitEnum $expression): self
    {
        $this->boundedVariables[str_replace('$', '', $variableName)] = $expression;

        return $this;
    }

    public function build(): ClassConfig
    {
        return new ClassConfig(
            $this->class,
            $this->aliases,
            $this->boundedVariables,
            $this->decorates,
            $this->singleton,
            $this->tags,
            $this->factory,
            array_values(array_unique($this->methodCalls)),
        );
    }

    public function call(string $method): self
    {
        $this->methodCalls[] = $method;

        return $this;
    }

    /**
     * @param class-string $id
     */
    public function decorates(
        string $id,
        int $priority = Decorator::DEFAULT_PRIORITY,
        string $signature = Decorator::DEFAULT_SIGNATURE,
    ): self {
        $this->decorates = DecoratorFactory::create($id, $priority, $signature);

        return $this;
    }

    public function factory(Factory $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    public function singleton(bool $singleton = true): self
    {
        $this->singleton = $singleton;

        return $this;
    }

    public function tag(string $name): self
    {
        $this->tags[] = $name;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Builder\Config;

use Temkaa\SimpleContainer\Factory\Definition\DecoratorFactory;
use Temkaa\SimpleContainer\Model\Config\ClassConfig;
use Temkaa\SimpleContainer\Model\Config\Decorator;

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
     * @var array<string, string>
     */
    private array $boundedVariables = [];

    private ?Decorator $decorates = null;

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

    public function bindVariable(string $variableName, string $expression): self
    {
        $this->boundedVariables[str_replace('$', '', $variableName)] = $expression;

        return $this;
    }

    public function build(): ClassConfig
    {
        return new ClassConfig(
            $this->class,
            array_values(array_unique($this->aliases)),
            $this->boundedVariables,
            $this->decorates,
            $this->singleton,
            $this->tags,
        );
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

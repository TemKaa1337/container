<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Builder\Config;

use Temkaa\SimpleContainer\Model\Config\Decorator;
use Temkaa\SimpleContainer\Model\Container\ClassConfig;

// TODO: add aliases here?
final class ClassBuilder
{
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
    public function __construct(
        private readonly string $class,
    ) {
    }

    public function bindVariable(string $variableName, string $expression): self
    {
        $this->boundedVariables[str_replace('$', '', $variableName)] = $expression;

        return $this;
    }

    public function build(): ClassConfig
    {
        return new ClassConfig($this->class, $this->boundedVariables, $this->decorates, $this->singleton, $this->tags);
    }

    public function decorates(
        string $id,
        int $priority = Decorator::DEFAULT_PRIORITY,
        string $signature = Decorator::DEFAULT_SIGNATURE,
    ): self {
        $this->decorates = new Decorator($id, $priority, str_replace('$', '', $signature));

        return $this;
    }

    /**
     * @param class-string $class
     */
    public static function make(string $class): self
    {
        return new self($class);
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

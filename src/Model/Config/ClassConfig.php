<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Config;

final readonly class ClassConfig
{
    /**
     * @param class-string          $class
     * @param array<string, string> $boundVariables
     * @param Decorator|null        $decorates
     * @param bool                  $singleton
     * @param string[]              $tags
     */
    public function __construct(
        private string $class,
        private array $boundVariables,
        private ?Decorator $decorates,
        private bool $singleton,
        private array $tags,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getBoundedVariables(): array
    {
        return $this->boundVariables;
    }

    /**
     * @return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function getDecorates(): ?Decorator
    {
        return $this->decorates;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function isSingleton(): bool
    {
        return $this->singleton;
    }
}

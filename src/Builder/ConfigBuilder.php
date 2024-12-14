<?php

declare(strict_types=1);

namespace Temkaa\Container\Builder;

use Temkaa\Container\Exception\Config\InvalidPathException;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Config\ClassConfig;
use function realpath;
use function sprintf;
use function str_replace;

/**
 * @api
 */
final class ConfigBuilder
{
    /**
     * @var array<class-string, class-string>
     */
    private array $boundedInterfaces = [];

    /**
     * @var array<string, mixed>
     */
    private array $boundedVariables = [];

    /**
     * @var array<class-string, ClassConfig>
     */
    private array $configuredClasses = [];

    /**
     * @var list<string>
     */
    private array $exclude = [];

    /**
     * @var list<string>
     */
    private array $include = [];

    /** @codeCoverageIgnore */
    public static function make(): self
    {
        return new self();
    }

    /**
     * @param class-string $interface
     * @param class-string $class
     */
    public function bindInterface(string $interface, string $class): self
    {
        $this->boundedInterfaces[$interface] = $class;

        return $this;
    }

    public function bindVariable(string $variableName, mixed $expression): self
    {
        $this->boundedVariables[str_replace('$', '', $variableName)] = $expression;

        return $this;
    }

    public function build(): Config
    {
        return new Config(
            $this->configuredClasses,
            $this->boundedInterfaces,
            $this->boundedVariables,
            $this->exclude,
            $this->include,
        );
    }

    public function configure(ClassConfig $class): self
    {
        $this->configuredClasses[$class->getClass()] = $class;

        return $this;
    }

    public function exclude(string $path): self
    {
        $this->exclude[] = $this->formatPath($path);

        return $this;
    }

    public function include(string $path): self
    {
        $this->include[] = $this->formatPath($path);

        return $this;
    }

    private function formatPath(string $sourcePath): string
    {
        $path = realpath($sourcePath);
        if ($path === false) {
            throw new InvalidPathException(sprintf('The specified path "%s" does not exist.', $sourcePath));
        }

        return $path;
    }
}

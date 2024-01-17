<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Attribute\NonAutowirable;
use Temkaa\SimpleContainer\Definition\Builder;
use Temkaa\SimpleContainer\Definition\Definition;
use Temkaa\SimpleContainer\Definition\Resolver;
use Temkaa\SimpleContainer\Exception\EntryNotFoundException;

#[NonAutowirable]
final class Container implements ContainerInterface
{
    /**
     * @var array<string, class-string>
     */
    private array $aliases = [];

    private Config $config;

    /**
     * @var array<class-string, Definition>
     */
    private array $definitions = [];

    private readonly array $env;

    private bool $isCompiled = false;

    public function __construct(array $config, array $env = [])
    {
        $this->env = $env;
        $this->config = new Config(config: $config, env: $env);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function compile(): void
    {
        if ($this->isCompiled) {
            return;
        }

        $definitionBuilder = new Builder($this->config, $this->env);
        $definitions = $definitionBuilder->build();

        $definitionResolver = new Resolver($definitions);
        $resolvedDefinitions = $definitionResolver->resolveAll();

        $this->definitions = array_combine(
            array_map(
                static fn (Definition $definition): string => $definition->getId(),
                $resolvedDefinitions,
            ),
            $resolvedDefinitions,
        );

        /** @var Definition $definition */
        foreach ($this->definitions as $id => $definition) {
            foreach ($definition->getAliases() as $alias) {
                $this->aliases[$alias] = $id;
            }
        }

        $this->isCompiled = true;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @return T
     *
     * @throws ContainerExceptionInterface
     */
    public function get(string $id): object
    {
        if (!$this->has($id)) {
            throw new EntryNotFoundException(sprintf('Could not find entry "%s".', $id));
        }

        $id = $this->aliases[$id] ?? $id;

        return $this->definitions[$id]->getInstance();
    }

    /**
     * @param class-string $id
     */
    public function has(string $id): bool
    {
        return isset($this->definitions[$id]) || isset($this->aliases[$id]);
    }
}

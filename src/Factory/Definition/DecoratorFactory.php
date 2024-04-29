<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Factory\Definition;

use ReflectionAttribute;
use Temkaa\SimpleContainer\Attribute\Decorates;
use Temkaa\SimpleContainer\Enum\Config\Structure;
use Temkaa\SimpleContainer\Model\Definition\Decorator;

final class DecoratorFactory
{
    public function createFromConfig(array $decoratorConfig): Decorator
    {
        $decoratesId = $decoratorConfig[Structure::Id->value];

        return $this->create(
            $decoratesId,
            priority: $decoratorConfig[Structure::Priority->value] ?? Decorates::DEFAULT_PRIORITY,
            signature: $decoratorConfig[Structure::Signature->value] ?? Decorates::DEFAULT_SIGNATURE,
            byInterface: interface_exists($decoratesId),
        );
    }

    /**
     * @template T of Decorates
     *
     * @param ReflectionAttribute<T> $reflection
     */
    public function createFromReflection(ReflectionAttribute $reflection): Decorator
    {
        $attribute = $reflection->newInstance();

        return $this->create(
            $attribute->id,
            $attribute->priority,
            $attribute->signature,
            byInterface: interface_exists($attribute->id),
        );
    }

    /**
     * @param class-string $id
     */
    private function create(string $id, int $priority, string $signature, bool $byInterface): Decorator
    {
        return (new Decorator())
            ->setId($id)
            ->setPriority($priority)
            ->setSignature(str_replace('$s', '', $signature))
            ->setByInterface($byInterface);
    }
}

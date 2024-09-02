<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Definition\Argument;

use ReflectionParameter;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Model\Config\Decorator;

final class DecoratorValidator
{
    /**
     * @param Decorator|null        $decorates
     * @param ReflectionParameter[] $arguments
     * @param class-string          $id
     *
     * @return void
     */
    public function validate(?Decorator $decorates, array $arguments, string $id): void
    {
        if (!$decorates || count($arguments) === 1) {
            return;
        }

        $argumentNames = array_map(
            static fn (ReflectionParameter $argument): string => $argument->getName(),
            $arguments,
        );

        if (!in_array($decorates->getSignature(), $argumentNames, strict: true)) {
            throw new UnresolvableArgumentException(
                sprintf(
                    'Could not resolve decorated class in class "%s" as it does not have argument named "%s".',
                    $id,
                    $decorates->getSignature(),
                ),
            );
        }
    }
}

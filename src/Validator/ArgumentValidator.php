<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator;

use Psr\Container\ContainerExceptionInterface;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Temkaa\SimpleContainer\Exception\UninstantiableEntryException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;

/**
 * @internal
 */
final readonly class ArgumentValidator
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function validate(ReflectionParameter $argument, string $id): void
    {
        $argumentType = $argument->getType();
        if (!$argumentType) {
            throw new UninstantiableEntryException(
                sprintf(
                    'Cannot instantiate entry with non-typed parameters "%s" -> "%s".',
                    $id,
                    $argument->getName(),
                ),
            );
        }

        if (!$argumentType instanceof ReflectionNamedType) {
            $formattedArgumentType = $argumentType instanceof ReflectionUnionType ? 'union' : 'intersection';

            throw new UnresolvableArgumentException(
                sprintf(
                    'Cannot resolve argument "%s" with %s type "%s" in class "%s".',
                    $argument->getName(),
                    $formattedArgumentType,
                    $argumentType,
                    $id,
                ),
            );
        }
    }
}

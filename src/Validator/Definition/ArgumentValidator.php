<?php

declare(strict_types=1);

namespace Temkaa\Container\Validator\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Temkaa\Container\Exception\UninstantiableEntryException;
use Temkaa\Container\Exception\UnresolvableArgumentException;

/**
 * @internal
 */
final readonly class ArgumentValidator
{
    /**
     * @param ReflectionParameter[] $arguments
     * @param class-string          $id
     *
     * @throws ContainerExceptionInterface
     */
    public function validate(array $arguments, string $id): void
    {
        foreach ($arguments as $argument) {
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
}

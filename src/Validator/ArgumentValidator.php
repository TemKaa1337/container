<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator;

use Psr\Container\ContainerExceptionInterface;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use Temkaa\SimpleContainer\Exception\UninstantiableEntryException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;

final readonly class ArgumentValidator
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function validate(ReflectionParameter $argument, string $id): void
    {
        // needed in order to suppress psalm undefined method messages
        /** @var ReflectionType&ReflectionNamedType $argumentType */
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

        if ($argumentType instanceof ReflectionUnionType || $argumentType instanceof ReflectionIntersectionType) {
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

        if (!$argumentType instanceof ReflectionNamedType) {
            throw new UnresolvableArgumentException(
                sprintf(
                    'Cannot instantiate entry "%s" with argument "%s::%s".',
                    $id,
                    $argument->getName(),
                    $argumentType->getName(),
                ),
            );
        }
    }
}

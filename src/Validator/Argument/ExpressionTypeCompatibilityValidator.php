<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Argument;

use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use UnitEnum;

final class ExpressionTypeCompatibilityValidator
{
    public function validate(string|UnitEnum $expression, ReflectionParameter $argument, string $id): void
    {
        $argumentName = $argument->getName();
        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();
        /** @var class-string $argumentTypeName */
        $argumentTypeName = $argumentType->getName();

        if (is_string($expression)) {
            if (!$argumentType->isBuiltin()) {
                throw new UnresolvableArgumentException(
                    sprintf(
                        'Cannot instantiate entry "%s" with argument "%s::%s" as bound expression has incompatible type "%s".',
                        $id,
                        $argumentName,
                        $argumentTypeName,
                        'string',
                    ),
                );
            }

            return;
        }

        if ($expression::class !== $argumentTypeName && !is_subclass_of($expression, $argumentTypeName)) {
            throw new UnresolvableArgumentException(
                sprintf(
                    'Cannot instantiate entry "%s" with argument "%s::%s" as bound expression has incompatible type "%s".',
                    $id,
                    $argumentName,
                    $argumentTypeName,
                    get_class($expression),
                ),
            );
        }
    }
}

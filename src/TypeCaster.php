<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer;

use Psr\Container\ContainerExceptionInterface;
use Temkaa\SimpleContainer\Exception\UnsupportedCastTypeException;

final class TypeCaster
{
    private const SUPPORTED_TYPES = ['bool', 'float', 'int', 'string', 'mixed'];

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws ContainerExceptionInterface
     */
    public function cast(mixed $value, string $castTo): mixed
    {
        if (!in_array($castTo, self::SUPPORTED_TYPES, strict: true)) {
            throw new UnsupportedCastTypeException(
                sprintf('Cannot cast value of type "%s" to "%s".', gettype($value), $castTo),
            );
        }

        switch ($castTo) {
            case 'bool':
                if (is_bool($value)) {
                    return $value;
                }

                if (in_array($value, ['true', 'false'], strict: true)) {
                    return $value === 'true';
                }

                return (bool) $value;
            case 'int':
                if (!is_numeric($value)) {
                    throw new UnsupportedCastTypeException(
                        sprintf('Cannot cast value of type "%s" to "%s".', gettype($value), $castTo),
                    );
                }

                return (int) $value;
            case 'float':
                if (!is_numeric($value)) {
                    throw new UnsupportedCastTypeException(
                        sprintf('Cannot cast value of type "%s" to "%s".', gettype($value), $castTo),
                    );
                }

                return (float) $value;
            case 'string':
                return (string) $value;
            case 'mixed':
                return $value;
            default:
                throw new UnsupportedCastTypeException(
                    sprintf('Cannot cast value of type "%s" to "%s".', gettype($value), $castTo),
                );
        }
    }
}

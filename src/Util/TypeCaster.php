<?php

declare(strict_types=1);

namespace Temkaa\Container\Util;

use Psr\Container\ContainerExceptionInterface;
use Temkaa\Container\Exception\UnsupportedCastTypeException;

/**
 * @internal
 */
final class TypeCaster
{
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @throws ContainerExceptionInterface
     */
    public static function cast(mixed $value, string $castTo): mixed
    {
        switch ($castTo) {
            case 'bool':
                if (in_array($value, ['true', 'false'], strict: true)) {
                    return $value === 'true';
                }

                return (bool) $value;
            case 'int':
            case 'float':
                if (!is_numeric($value)) {
                    throw new UnsupportedCastTypeException(
                        sprintf('Cannot cast value of type "%s" to "%s".', gettype($value), $castTo),
                    );
                }

                return $castTo === 'int' ? (int) $value : (float) $value;
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

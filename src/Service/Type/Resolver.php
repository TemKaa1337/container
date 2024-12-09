<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Type;

use BackedEnum;
use ReflectionNamedType;
use ReflectionParameter;
use Stringable;
use Temkaa\Container\Exception\UnresolvableArgumentException;
use Temkaa\Container\Model\Value;
use function class_implements;
use function get_debug_type;
use function in_array;
use function is_array;
use function is_bool;
use function is_callable;
use function is_iterable;
use function is_numeric;
use function is_object;
use function is_subclass_of;
use function sprintf;

/**
 * @internal
 */
final readonly class Resolver
{
    /**
     * @param class-string $definitionId
     */
    public function resolve(mixed $value, ReflectionParameter $argument, string $definitionId): mixed
    {
        $argumentName = $argument->getName();
        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();
        $argumentTypeName = $argumentType->getName();
        $valueDebugType = get_debug_type($value);

        $result = match ($argumentTypeName) {
            'string'        => $this->resolveString($value),
            'int'           => $this->resolveInt($value),
            'float'         => $this->resolveFloat($value),
            'bool'          => $this->resolveBoolean($value),
            'true', 'false' => $this->resolveAtomicBoolean($value, $argumentTypeName),
            'null'          => $this->resolveNull($value),
            'array'         => $this->resolveArray($value),
            'iterable'      => $this->resolveIterable($value),
            'callable'      => $this->resolveCallable($value),
            'mixed'         => new Value($value, resolved: true),
            default         => $this->resolveObject($value, $argumentTypeName),
        };

        if (!$result->resolved) {
            throw new UnresolvableArgumentException(
                sprintf(
                    'Cannot instantiate entry "%s" with argument "%s::%s" as bound expression has incompatible type "%s".',
                    $definitionId,
                    $argumentName,
                    $argumentTypeName,
                    $valueDebugType,
                ),
            );
        }

        return $result->value;
    }

    private function resolveArray(mixed $value): Value
    {
        if (is_array($value)) {
            return new Value($value, resolved: true);
        }

        return new Value($value, resolved: false);
    }

    private function resolveAtomicBoolean(mixed $value, string $argumentType): Value
    {
        if (is_bool($value)) {
            if ($argumentType === 'false') {
                if ($value) {
                    return new Value(null, resolved: false);
                }

                return new Value(false, resolved: true);
            }

            if ($value) {
                return new Value(true, resolved: true);
            }

            return new Value(null, resolved: false);
        }

        if (in_array($value, ['true', 'false', '1', '0'], true)) {
            $trueValues = ['true', '1'];
            if ($argumentType === 'false') {
                if (in_array($value, $trueValues, true)) {
                    return new Value(null, resolved: false);
                }

                return new Value(false, resolved: true);
            }

            if (in_array($value, $trueValues, true)) {
                return new Value(true, resolved: true);
            }

            return new Value(null, resolved: false);
        }

        return new Value(null, resolved: false);
    }

    private function resolveBoolean(mixed $value): Value
    {
        if (is_bool($value)) {
            return new Value($value, resolved: true);
        }

        if (in_array($value, ['true', 'false'], true)) {
            return new Value($value === 'true', resolved: true);
        }

        if (in_array($value, ['1', '0'], true)) {
            return new Value($value === '1', resolved: true);
        }

        return new Value(null, resolved: false);
    }

    private function resolveCallable(mixed $value): Value
    {
        if (is_callable($value)) {
            return new Value($value, resolved: true);
        }

        return new Value($value, resolved: false);
    }

    private function resolveFloat(mixed $value): Value
    {
        if (is_numeric($value)) {
            return new Value((float) $value, resolved: true);
        }

        if ($value instanceof BackedEnum && is_numeric($value->value)) {
            return new Value((float) $value->value, resolved: true);
        }

        return new Value(null, resolved: false);
    }

    private function resolveInt(mixed $value): Value
    {
        if (is_numeric($value)) {
            return new Value((int) $value, resolved: true);
        }

        if ($value instanceof BackedEnum && is_numeric($value->value)) {
            return new Value((int) $value->value, resolved: true);
        }

        return new Value(null, resolved: false);
    }

    private function resolveIterable(mixed $value): Value
    {
        if (is_iterable($value)) {
            return new Value($value, resolved: true);
        }

        return new Value($value, resolved: false);
    }

    private function resolveNull(mixed $value): Value
    {
        if ($value === null) {
            return new Value($value, resolved: true);
        }

        return new Value($value, resolved: false);
    }

    private function resolveObject(mixed $value, string $type): Value
    {
        if (!is_object($value)) {
            return new Value(null, resolved: false);
        }

        /** @var class-string|'object' $type */
        if ($type === 'object') {
            return new Value($value, resolved: true);
        }

        $valueClass = $value::class;
        if ($valueClass === $type || is_subclass_of($value, $type)) {
            return new Value($value, resolved: true);
        }

        return new Value(null, resolved: false);
    }

    private function resolveString(mixed $value): Value
    {
        $type = get_debug_type($value);
        if (in_array($type, ['int', 'string', 'float'], true)) {
            return new Value((string) $value, resolved: true);
        }

        if ($type === 'bool') {
            return new Value($value ? 'true' : 'false', resolved: true);
        }

        if ($value instanceof BackedEnum) {
            return new Value((string) $value->value, resolved: true);
        }

        if (
            is_object($value)
            && in_array(Stringable::class, class_implements($value), true)
        ) {
            /** @psalm-suppress InvalidCast */
            return new Value((string) $value, resolved: true);
        }

        return new Value(null, resolved: false);
    }
}

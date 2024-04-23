<?php

declare(strict_types=1);

namespace Tests\Unit;

use Closure;
use Generator;
use ReflectionAttribute;
use ReflectionClass;
use Temkaa\SimpleContainer\Exception\UnsupportedCastTypeException;
use Tests\Helper\Service\ClassGenerator;

abstract class AbstractContainerTestCase extends AbstractUnitTestCase
{
    protected const GENERATED_CLASS_STUB_PATH = '/../Fixture/Stub/Class/';
    protected const GENERATED_CONFIG_STUB_PATH = '/../Fixture/Stub/Config/';

    public static function getDataForCompilesWithUninstantiableEntryTest(): iterable
    {
        yield [ClassGenerator::getClassName(), 'abstract class', [], 'public'];

        yield [ClassGenerator::getClassName(), 'final class', [], 'private'];

        yield [ClassGenerator::getClassName(), 'final class', [], 'protected'];

        yield [
            ClassGenerator::getClassName(),
            'final class',
            [self::ATTRIBUTE_NON_AUTOWIRABLE_SIGNATURE],
            'public',
        ];
    }

    public static function getDataForDoesNotCompileDueToInternalClassDependencyTest(): iterable
    {
        yield [
            ClassGenerator::getClassName(),
            'public readonly \Closure $generator',
            Closure::class,
        ];
        yield [
            ClassGenerator::getClassName(),
            'public readonly \Generator $generator',
            Generator::class,
        ];
        yield [
            ClassGenerator::getClassName(),
            'public readonly \ReflectionClass $r',
            ReflectionClass::class,
        ];
        yield [
            ClassGenerator::getClassName(),
            'public readonly \ReflectionAttribute $r',
            ReflectionAttribute::class,
        ];
    }

    public static function getDataForDoesNotCompileDueToNotDeterminedArgumentTypeTest(): iterable
    {
        yield [
            $className = ClassGenerator::getClassName(),
            'public readonly array|string $arg',
            sprintf(
                'Cannot resolve argument "arg" with union type "array|string" in class "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
            ),
        ];

        yield [
            $className = ClassGenerator::getClassName(),
            'public readonly array|object $arg',
            sprintf(
                'Cannot resolve argument "arg" with union type "object|array" in class "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
            ),
        ];

        yield [
            $className = ClassGenerator::getClassName(),
            'public readonly \Generator&\Iterator $arg',
            sprintf(
                'Cannot resolve argument "arg" with intersection type "Generator&Iterator" in class "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
            ),
        ];

        yield [
            $className = ClassGenerator::getClassName(),
            'public readonly (\Generator&\Iterator)|array $arg',
            sprintf(
                'Cannot resolve argument "arg" with union type "(Generator&Iterator)|array" in class "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
            ),
        ];
    }

    public static function getDataForDoesNotCompileDueToVariableBindingErrorsTest(): iterable
    {
        yield [
            ClassGenerator::getClassName(),
            ['public readonly object $arg'],
            UnsupportedCastTypeException::class,
            sprintf('Cannot cast value of type "%s" to "%s".', 'string', 'object'),
        ];

        yield [
            ClassGenerator::getClassName(),
            ['public readonly array $arg'],
            UnsupportedCastTypeException::class,
            sprintf('Cannot cast value of type "%s" to "%s".', 'string', 'array'),
        ];

        yield [
            ClassGenerator::getClassName(),
            ['public readonly iterable $arg'],
            UnsupportedCastTypeException::class,
            sprintf('Cannot cast value of type "%s" to "%s".', 'string', 'iterable'),
        ];

        yield [
            ClassGenerator::getClassName(),
            ['public readonly int $arg'],
            UnsupportedCastTypeException::class,
            sprintf('Cannot cast value of type "%s" to "%s".', 'string', 'int'),
        ];

        yield [
            ClassGenerator::getClassName(),
            ['public readonly float $arg'],
            UnsupportedCastTypeException::class,
            sprintf('Cannot cast value of type "%s" to "%s".', 'string', 'float'),
        ];
    }

    public static function getDataForDoesNotCompileWithUninstantiableEntryTest(): iterable
    {
        yield [
            ClassGenerator::getClassName(),
            $invalidClassName = ClassGenerator::getClassName(),
            'abstract class',
            'public',
            [sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$invalidClassName)],
        ];

        yield [
            ClassGenerator::getClassName(),
            $invalidClassName = ClassGenerator::getClassName(),
            'final class',
            'protected',
            [sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$invalidClassName)],
        ];

        yield [
            ClassGenerator::getClassName(),
            $invalidClassName = ClassGenerator::getClassName(),
            'final class',
            'private',
            [sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$invalidClassName)],
        ];
    }
}

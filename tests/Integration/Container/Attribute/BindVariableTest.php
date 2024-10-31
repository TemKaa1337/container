<?php

declare(strict_types=1);

namespace Container\Attribute;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Container\Builder\ContainerBuilder;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

/**
 * @psalm-suppress ArgumentTypeCoercion, MixedPropertyFetch, MixedAssignment
 */
final class BindVariableTest extends AbstractContainerTestCase
{
    /**
     * @psalm-suppress InvalidClassConstantType
     */
    protected const string GENERATED_CLASS_STUB_PATH = '/../../../Fixture/Stub/Class/';

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompileWithCastedBoundVariables(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly int $varOne,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly string $varTwo,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly float $varThree,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly bool $varFour,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_FLOAT_VAR)'),
                        'public readonly mixed $varFive,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_BOOL_VAL)'),
                        'public readonly mixed $varSix,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_INT_VAL)'),
                        'public readonly mixed $varSeven,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_STRING_VAL)'),
                        'public readonly mixed $varEight,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_BOOL_VAL)'),
                        'public readonly bool $varNine,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_VAR_4)'),
                        'public readonly bool $varTen,',
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);

        self::assertEquals(10, $class->varOne);
        self::assertEquals('10.1', $class->varTwo);
        self::assertEquals(10.1, $class->varThree);
        self::assertTrue($class->varFour);
        self::assertEquals('10.1', $class->varFive);
        self::assertEquals('false', $class->varSix);
        self::assertEquals('3', $class->varSeven);
        self::assertEquals('string', $class->varEight);
        self::assertFalse($class->varNine);
        self::assertTrue($class->varTen);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompileWithEnvVariableReferencingAnotherVariable(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_VARIABLE_REFERENCE)'),
                        'public readonly string $envReference,',
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertEquals('string_additional_string', $class->envReference);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesNonSingletonWithBoundVariables(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_VARIABLE_REFERENCE)'),
                        'public readonly string $envReference1,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_VAR_3)'),
                        'public readonly string $envReference2,',
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class2);
        self::assertNotSame($class1, $class2);
        self::assertEquals('string_additional_string', $class1->envReference1);
        self::assertEquals('string_additional_string', $class2->envReference1);
        self::assertEquals('test-three', $class1->envReference2);
        self::assertEquals('test-three', $class2->envReference2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithCastingStrings(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, '10.1'),
                        'public readonly int $varOne,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, '10.1'),
                        'public readonly string $varTwo,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, '10.1'),
                        'public readonly float $varThree,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, '10.1'),
                        'public readonly bool $varFour,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, '10.1'),
                        'public readonly mixed $varFive,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'false'),
                        'public readonly mixed $varSix,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, '3'),
                        'public readonly mixed $varSeven,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'string'),
                        'public readonly mixed $varEight,',
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);

        self::assertEquals(10, $class->varOne);
        self::assertEquals('10.1', $class->varTwo);
        self::assertEquals(10.1, $class->varThree);
        self::assertTrue($class->varFour);
        self::assertEquals('10.1', $class->varFive);
        self::assertEquals('false', $class->varSix);
        self::assertEquals('3', $class->varSeven);
        self::assertEquals('string', $class->varEight);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithEnums(): void
    {
        $className = ClassGenerator::getClassName();
        $unitEnum = ClassGenerator::getClassName().'UnitEnum';
        $backedEnum = ClassGenerator::getClassName().'BackedEnum';
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$unitEnum.php")
                    ->setName($unitEnum)
                    ->setPrefix('enum')
                    ->setBody([
                        'case EnumCaseOne;',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$backedEnum.php")
                    ->setName($backedEnum)
                    ->setPrefix('enum')
                    ->setPostfix(': string')
                    ->setBody([
                        "case EnumCaseOne = 'enum_case_one';",
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_PARAMETER_RAW_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$unitEnum.'::EnumCaseOne',
                        ),
                        'public readonly \UnitEnum $abstractUnitEnum,',
                        sprintf(
                            self::ATTRIBUTE_PARAMETER_RAW_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$backedEnum.'::EnumCaseOne',
                        ),
                        'public readonly \BackedEnum $abstractBackedEnum,',
                        sprintf(
                            self::ATTRIBUTE_PARAMETER_RAW_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$unitEnum.'::EnumCaseOne',
                        ),
                        sprintf(
                            'public readonly %s $concreteUnitEnum,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$unitEnum,
                        ),
                        sprintf(
                            self::ATTRIBUTE_PARAMETER_RAW_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$backedEnum.'::EnumCaseOne',
                        ),
                        sprintf(
                            'public readonly %s $concreteBackedEnum,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$backedEnum,
                        ),
                    ]),
            )
            ->generate();
        $unitEnumValue = constant(self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$unitEnum.'::EnumCaseOne');
        $backedEnumValue = constant(self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$backedEnum.'::EnumCaseOne');

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$backedEnum.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$unitEnum.'.php',
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$unitEnum, $class->abstractUnitEnum);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$unitEnum, $class->concreteUnitEnum);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$backedEnum, $class->abstractBackedEnum);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$backedEnum, $class->concreteBackedEnum);

        self::assertEquals($unitEnumValue, $class->abstractUnitEnum);
        self::assertEquals($unitEnumValue, $class->concreteUnitEnum);
        self::assertEquals($backedEnumValue, $class->abstractBackedEnum);
        self::assertEquals($backedEnumValue, $class->concreteBackedEnum);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithMissingEnvVariableAndDefaultValue(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_SOME_NON_EXISTING_VARIABLE)'),
                        'public readonly int $variable = 5,',
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);
        self::assertSame(5, $class->variable);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithMultipleEnvVarsInSingleBoundVariable(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE,
                            '123',
                        ),
                        'public readonly string $arg,',
                    ]),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];

        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: ['arg' => 'env(ENV_VAR_1)env(ENV_VAR_2)_env(ENV_VAR_3)-TEST-env(ENV_VAR_4)'],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);
        self::assertEquals('test_one10.1_test-three-TEST-true', $class->arg);
    }
}

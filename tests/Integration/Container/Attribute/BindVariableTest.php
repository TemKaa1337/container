<?php

declare(strict_types=1);

namespace Container\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;
use Temkaa\Container\Exception\UnresolvableArgumentException;
use Tests\Fixture\Stub\AbstractClass1;
use Tests\Fixture\Stub\AbstractClass2;
use Tests\Fixture\Stub\BackedEnum;
use Tests\Fixture\Stub\ClassExtends1;
use Tests\Fixture\Stub\ClassExtends2;
use Tests\Fixture\Stub\ClassImplements1;
use Tests\Fixture\Stub\ClassImplements2;
use Tests\Fixture\Stub\EmptyClass;
use Tests\Fixture\Stub\Interface1;
use Tests\Fixture\Stub\Interface2;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;
use function sprintf;

/**
 * @psalm-suppress all
 * @SuppressWarnings(PHPMD)
 */
final class BindVariableTest extends AbstractContainerTestCase
{
    /**
     * @psalm-suppress InvalidClassConstantType
     */
    protected const string GENERATED_CLASS_STUB_PATH = '/../../../Fixture/Stub/Class/';

    public static function getDataForCompilableBoundVariable(): iterable
    {
        // variableType, boundValue, expectedCompiledValue,
        yield ['string', '"string"', 'string'];
        yield ['string', '"env(APP_BOUND_VAR)"', 'bound_variable_value'];
        yield ['string', '"env(ENV_CASTABLE_STRING_VAR)"', '10.1'];
        yield ['string', '"env(ENV_VARIABLE_REFERENCE)"', 'string_additional_string'];
        yield ['string', '10', '10'];
        yield ['string', '10.1', '10.1'];
        yield ['string', 'false', 'false'];
        yield ['string', 'true', 'true'];
        yield ['string', '\\'.BackedEnum::class.'::TestCase', 'TestCase'];

        yield ['int', '10', 10];
        yield ['int', '"10"', 10];
        yield ['int', '10.6', 10];
        yield ['int', '"env(ENV_CASTABLE_STRING_VAR)"', 10];
        yield ['int', '"env(ENV_INT_VAL)"', 3];

        yield ['float', '"10"', 10.0];
        yield ['float', '10', 10.0];
        yield ['float', '10.6', 10.6];
        yield ['float', '"env(ENV_CASTABLE_STRING_VAR)"', 10.1];
        yield ['float', '"env(ENV_INT_VAL)"', 3.0];

        yield ['null', 'null', null];

        yield ['bool', 'false', false];
        yield ['bool', 'true', true];
        yield ['bool', '"false"', false];
        yield ['bool', '"true"', true];
        yield ['bool', '"0"', false];
        yield ['bool', '"1"', true];

        yield ['false', 'false', false];
        yield ['false', '"false"', false];
        yield ['false', '"0"', false];

        yield ['true', 'true', true];
        yield ['true', '"true"', true];
        yield ['true', '"1"', true];

        yield ['array', '[1, 2, 3]', [1, 2, 3]];
        yield ['array', "['a' => 'a']", ['a' => 'a']];

        yield ['iterable', "['a' => 'a']", ['a' => 'a']];

        yield ['\UnitEnum', '\\'.IteratorFormat::class.'::List', IteratorFormat::List];
        yield ['\UnitEnum', '\\'.BackedEnum::class.'::TestCase', BackedEnum::TestCase];

        yield ['\\'.IteratorFormat::class, '\\'.IteratorFormat::class.'::List', IteratorFormat::List];

        yield ['\BackedEnum', '\\'.BackedEnum::class.'::TestCase', BackedEnum::TestCase];
        yield ['\\'.BackedEnum::class, '\\'.BackedEnum::class.'::TestCase', BackedEnum::TestCase];

        // - mixed
        yield ['mixed', '"string"', 'string'];
        yield ['mixed', '"env(APP_BOUND_VAR)"', 'bound_variable_value'];
        yield ['mixed', '"env(ENV_CASTABLE_STRING_VAR)"', '10.1'];
        yield ['mixed', '"env(ENV_VARIABLE_REFERENCE)"', 'string_additional_string'];
        yield ['mixed', '10', 10];
        yield ['mixed', '10.1', 10.1];
        yield ['mixed', 'false', false];
        yield ['mixed', 'true', true];
        yield ['mixed', 'null', null];
        yield ['mixed', '\\'.BackedEnum::class.'::TestCase', BackedEnum::TestCase];
        yield ['mixed', '[1, 2, 3]', [1, 2, 3]];
        yield ['mixed', "['a' => 'a']", ['a' => 'a']];

        $classExtends1String = sprintf('new \%s()', ClassExtends1::class);
        $classExtends2String = sprintf('new \%s()', ClassExtends2::class);
        $classImplements1String = sprintf('new \%s()', ClassImplements1::class);
        $classImplements2String = sprintf('new \%s()', ClassImplements2::class);

        $classExtends1 = new ClassExtends1();
        $classExtends2 = new ClassExtends2();
        $classImplements1 = new ClassImplements1();
        $classImplements2 = new ClassImplements2();

        yield ['mixed', $classExtends1String, $classExtends1];
        yield ['mixed', $classExtends2String, $classExtends2];
        yield ['mixed', $classImplements1String, $classImplements1];
        yield ['mixed', $classImplements2String, $classImplements2];

        yield ['object', $classExtends1String, $classExtends1];
        yield ['object', $classExtends2String, $classExtends2];
        yield ['object', $classImplements1String, $classImplements1];
        yield ['object', $classImplements2String, $classImplements2];

        yield ['\\'.AbstractClass1::class, $classExtends1String, $classExtends1];
        yield ['\\'.AbstractClass1::class, $classExtends2String, $classExtends2];
        yield ['\\'.ClassExtends1::class, $classExtends1String, $classExtends1];
        yield ['\\'.AbstractClass2::class, $classExtends2String, $classExtends2];
        yield ['\\'.ClassExtends2::class, $classExtends2String, $classExtends2];

        yield ['\\'.Interface1::class, $classImplements1String, $classImplements1];
        yield ['\\'.Interface1::class, $classImplements2String, $classImplements2];
        yield ['\\'.ClassImplements1::class, $classImplements1String, $classImplements1];
        yield ['\\'.Interface2::class, $classImplements2String, $classImplements2];
        yield ['\\'.ClassImplements2::class, $classImplements2String, $classImplements2];

        // TODO: add more tests on mixed
        // TODO: add more tests on standalone null
        // TODO: add functionality with new Instance
    }

    public static function getDataForNonCompilableBoundVariable(): iterable
    {
        // variableType, boundValue
        yield ['string', '\\'.IteratorFormat::class.'::List', IteratorFormat::class];
        yield ['string', '[]', 'array'];
        yield ['string', 'null', 'null'];

        yield ['int', '"string"', 'string'];
        yield ['int', 'true', 'bool'];
        yield ['int', '[]', 'array'];
        yield ['int', '"env(ENV_STRING_VAL)"', 'string'];
        yield ['int', 'null', 'null'];

        yield ['float', '"string"', 'string'];
        yield ['float', 'true', 'bool'];
        yield ['float', '[]', 'array'];
        yield ['float', '"env(ENV_STRING_VAL)"', 'string'];
        yield ['float', 'null', 'null'];

        yield ['null', '"string"', 'string'];
        yield ['null', 'true', 'bool'];
        yield ['null', '[]', 'array'];
        yield ['null', '"env(ENV_STRING_VAL)"', 'string'];

        yield ['bool', '"string"', 'string'];
        yield ['bool', '10', 'int'];
        yield ['bool', '10.1', 'float'];
        yield ['bool', '[]', 'array'];
        yield ['bool', '"env(ENV_STRING_VAL)"', 'string'];
        yield ['bool', 'null', 'null'];

        yield ['false', 'true', 'bool'];
        yield ['false', '"1"', 'string'];
        yield ['false', 'null', 'null'];
        yield ['true', 'false', 'bool'];
        yield ['true', '"0"', 'string'];
        yield ['true', 'null', 'null'];

        yield ['array', '"string"', 'string'];
        yield ['array', '10', 'int'];
        yield ['array', '10.1', 'float'];
        yield ['array', '"env(ENV_STRING_VAL)"', 'string'];
        yield ['array', 'null', 'null'];

        yield ['iterable', '"string"', 'string'];
        yield ['iterable', '10', 'int'];
        yield ['iterable', '10.1', 'float'];
        yield ['iterable', '"env(ENV_STRING_VAL)"', 'string'];
        yield ['iterable', 'null', 'null'];

        yield ['\Traversable', '"string"', 'string'];
        yield ['\Traversable', '10', 'int'];
        yield ['\Traversable', '10.1', 'float'];
        yield ['\Traversable', '"env(ENV_STRING_VAL)"', 'string'];
        yield ['\Traversable', "null", 'null'];
        yield ['\Traversable', '[]', 'array'];

        yield ['\Closure', '"string"', 'string'];
        yield ['\Closure', '10', 'int'];
        yield ['\Closure', '10.1', 'float'];
        yield ['\Closure', '"env(ENV_STRING_VAL)"', 'string'];
        yield ['\Closure', 'null', 'null'];
        yield ['\Closure', '"string"', 'string'];
        yield ['\Closure', '[]', 'array'];

        yield ['object', '"string"', 'string'];
        yield ['object', '10', 'int'];
        yield ['object', '10.1', 'float'];
        yield ['object', '"env(ENV_STRING_VAL)"', 'string'];
        yield ['object', 'null', 'null'];
        yield ['object', '"string"', 'string'];
        yield ['object', '[]', 'array'];

        yield ['\BackedEnum', '\\'.IteratorFormat::class.'::List', IteratorFormat::class];
        yield ['\\'.BackedEnum::class, '\\'.IteratorFormat::class.'::List', IteratorFormat::class];
        yield ['\\'.IteratorFormat::class, '\\'.BackedEnum::class.'::TestCase', BackedEnum::class];

        $classExtends1String = sprintf('new \%s()', ClassExtends1::class);
        $classExtends2String = sprintf('new \%s()', ClassExtends2::class);
        $classImplements1String = sprintf('new \%s()', ClassImplements1::class);
        $classImplements2String = sprintf('new \%s()', ClassImplements2::class);
        $emptyClassString = sprintf('new \%s()', EmptyClass::class);

        yield ['\\'.AbstractClass1::class, $emptyClassString, EmptyClass::class];
        yield ['\\'.ClassExtends1::class, $emptyClassString, EmptyClass::class];
        yield ['\\'.ClassExtends1::class, $classExtends2String, ClassExtends2::class];
        yield ['\\'.ClassExtends2::class, $classExtends1String, ClassExtends1::class];
        yield ['\\'.AbstractClass2::class, $emptyClassString, EmptyClass::class];
        yield ['\\'.AbstractClass2::class, $classExtends1String, ClassExtends1::class];

        yield ['\\'.Interface1::class, $emptyClassString, EmptyClass::class];
        yield ['\\'.Interface1::class, $classExtends1String, ClassExtends1::class];
        yield ['\\'.Interface1::class, $classExtends2String, ClassExtends2::class];
        yield ['\\'.ClassImplements1::class, $classImplements2String, ClassImplements2::class];
        yield ['\\'.ClassImplements2::class, $classImplements1String, ClassImplements1::class];
        yield ['\\'.Interface2::class, $emptyClassString, EmptyClass::class];
        yield ['\\'.Interface2::class, $classExtends1String, ClassExtends1::class];
        yield ['\\'.Interface2::class, $classExtends2String, ClassExtends2::class];
        yield ['\\'.Interface2::class, $classImplements1String, ClassImplements1::class];

        // TODO: add more tests on mixed
        // TODO: add functionality with new Instance
    }

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
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_BOOL_VAL)'),
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
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, '1'),
                        'public readonly bool $varEleven,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, '0'),
                        'public readonly bool $varTwelve,',
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_VAR_4)'),
                        'public readonly bool $varThirteen,',
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
        self::assertFalse($class->varFour);
        self::assertEquals('10.1', $class->varFive);
        self::assertEquals('false', $class->varSix);
        self::assertEquals('3', $class->varSeven);
        self::assertEquals('string', $class->varEight);
        self::assertFalse($class->varNine);
        self::assertTrue($class->varTen);
        self::assertTrue($class->varEleven);
        self::assertFalse($class->varTwelve);
        self::assertTrue($class->varThirteen);
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

    #[DataProvider('getDataForCompilableBoundVariable')]
    public function testCompilesWithConstructorBoundVariable(
        string $variableType,
        string $boundValue,
        mixed $expectedCompiledValue,
    ): void {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_PARAMETER_RAW_SIGNATURE,
                            $boundValue,
                        ),
                        "public readonly $variableType \$variable,",
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php']);

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);
        self::assertEquals($expectedCompiledValue, $class->variable);
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

    #[DataProvider('getDataForCompilableBoundVariable')]
    public function testCompilesWithFactoryBoundVariable(
        string $variableType,
        string $boundValue,
        mixed $expectedCompiledValue,
    ): void {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $variable,',
                            $variableType === 'callable' ? '\Closure' : $variableType,
                        ),
                    ])
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setBody(
                        [
                            sprintf(
                                <<<'BODY'
                                public function create(%s %s $variable): %s
                                {
                                    return new %s($variable);
                                }
                                BODY,
                                sprintf(
                                    self::ATTRIBUTE_PARAMETER_RAW_SIGNATURE,
                                    $boundValue,
                                ),
                                $variableType,
                                self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                                self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            ),
                        ],
                    ),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class);
        self::assertEquals($expectedCompiledValue, $class->variable);
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

    #[DataProvider('getDataForCompilableBoundVariable')]
    public function testCompilesWithRequiredMethodCallBoundVariable(
        string $variableType,
        string $boundValue,
        mixed $expectedCompiledValue,
    ): void {
        $className1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        sprintf(
                            'public readonly %s $variable;',
                            $variableType === 'callable' ? '\Closure' : $variableType,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'BODY'
                                public function required(%s %s $variable): void
                                {
                                    $this->variable = $variable;
                                }
                                BODY,
                            sprintf(
                                self::ATTRIBUTE_PARAMETER_RAW_SIGNATURE,
                                $boundValue,
                            ),
                            $variableType,
                        ),
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class);
        $this->assertInitialized($class, 'variable');
        self::assertEquals($expectedCompiledValue, $class->variable);
    }

    #[DataProvider('getDataForNonCompilableBoundVariable')]
    public function testDoesNotCompileWithConstructorBoundVariable(
        string $variableType,
        string $boundValue,
        string $expectedType,
    ): void {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_PARAMETER_RAW_SIGNATURE,
                            $boundValue,
                        ),
                        "public readonly $variableType \$variable,",
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
        );

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "variable::%s" as bound expression has incompatible type "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
                ltrim($variableType, '\\'),
                $expectedType,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    #[DataProvider('getDataForNonCompilableBoundVariable')]
    public function testDoesNotCompileWithFactoryBoundVariable(
        string $variableType,
        string $boundValue,
        string $expectedType,
    ): void {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $variable,',
                            $variableType === 'callable' ? '\Closure' : $variableType,
                        ),
                    ])
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setBody(
                        [
                            sprintf(
                                <<<'BODY'
                                public function create(%s %s $variable): %s
                                {
                                    return new %s($variable);
                                }
                                BODY,
                                sprintf(
                                    self::ATTRIBUTE_PARAMETER_RAW_SIGNATURE,
                                    $boundValue,
                                ),
                                $variableType,
                                self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                                self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            ),
                        ],
                    ),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            ],
        );

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "variable::%s" as bound expression has incompatible type "%s".',
                self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                ltrim($variableType, '\\'),
                $expectedType,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    #[DataProvider('getDataForNonCompilableBoundVariable')]
    public function testDoesNotCompileWithRequiredMethodCallBoundVariable(
        string $variableType,
        string $boundValue,
        string $expectedType,
    ): void {
        $className1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        sprintf(
                            'public readonly %s $variable;',
                            $variableType === 'callable' ? '\Closure' : $variableType,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'BODY'
                                public function required(%s %s $variable): void
                                {
                                    $this->variable = $variable;
                                }
                                BODY,
                            sprintf(
                                self::ATTRIBUTE_PARAMETER_RAW_SIGNATURE,
                                $boundValue,
                            ),
                            $variableType,
                        ),
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            ],
        );

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "variable::%s" as bound expression has incompatible type "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className1,
                ltrim($variableType, '\\'),
                $expectedType,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }
}

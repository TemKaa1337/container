<?php

declare(strict_types=1);

namespace Container\Config;

use Closure;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Stringable;
use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
use Temkaa\Container\Attribute\Bind\TaggedIterator;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;
use Temkaa\Container\Exception\UnresolvableArgumentException;
use Temkaa\Container\Model\Config\Factory;
use Tests\Fixture\Stub\AbstractClass1;
use Tests\Fixture\Stub\AbstractClass2;
use Tests\Fixture\Stub\ClassExtends1;
use Tests\Fixture\Stub\ClassExtends2;
use Tests\Fixture\Stub\ClassImplements1;
use Tests\Fixture\Stub\ClassImplements2;
use Tests\Fixture\Stub\EmptyClass;
use Tests\Fixture\Stub\IntBackedEnum;
use Tests\Fixture\Stub\Interface1;
use Tests\Fixture\Stub\Interface2;
use Tests\Fixture\Stub\StringBackedEnum;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\AbstractTestCase;
use Tests\Integration\Container\AbstractContainerTestCase;
use function constant;
use function fopen;
use function get_debug_type;
use function ltrim;
use function realpath;
use function sprintf;

/**
 * @psalm-suppress all
 * @SuppressWarnings(PHPMD)
 */
// TODO: add tests on non compilable bind variable
// TODO: add tests attribute compilable/non-compilable bind variable
// TODO: add self type to compilable and non-compilable
final class BindVariableTest extends AbstractContainerTestCase
{
    /**
     * @psalm-suppress InvalidClassConstantType
     */
    protected const string GENERATED_CLASS_STUB_PATH = '/../../../Fixture/Stub/Class/';

    public static function getCallableDataForCompilableBoundVariable(): iterable
    {
        $callback = static fn (): string => 'string';
        yield ['\Closure', $callback, $callback];
        yield ['callable', $callback, $callback];

        $callback = fn (): string => 'string';
        yield ['\Closure', $callback, $callback];
        yield ['callable', $callback, $callback];
    }

    public static function getCallableDataForNonCompilableBoundVariable(): iterable
    {
        $object = new class {
        };
        yield ['callable', 'string'];
        yield ['callable', true];
        yield ['callable', []];
        yield ['callable', 'env(ENV_STRING_VAL)'];
        yield ['callable', $object];
        yield ['callable', null];

        yield ['\Closure', 'string'];
        yield ['\Closure', true];
        yield ['\Closure', []];
        yield ['\Closure', 'env(ENV_STRING_VAL)'];
        yield ['\Closure', $object];
        yield ['\Closure', null];
    }

    public static function getDataForCompilableBoundVariable(): iterable
    {
        // variableType, boundValue, expectedCompiledValue,
        yield ['string', 'string', 'string'];
        yield ['string', 'env(APP_BOUND_VAR)', 'bound_variable_value'];
        yield ['string', 'env(ENV_CASTABLE_STRING_VAR)', '10.1'];
        yield ['string', 'env(ENV_VARIABLE_REFERENCE)', 'string_additional_string'];
        yield ['string', 10, '10'];
        yield ['string', 10.1, '10.1'];
        yield ['string', false, 'false'];
        yield ['string', true, 'true'];
        yield ['string', StringBackedEnum::TestCase, 'TestCase'];
        yield ['string', IntBackedEnum::TestCase, '1'];
        yield [
            'string',
            new class implements Stringable {
                public function __toString(): string
                {
                    return 'string';
                }
            },
            'string',
        ];

        yield ['int', 10, 10];
        yield ['int', IntBackedEnum::TestCase, 1];
        yield ['int', StringBackedEnum::NumericCase, 10];
        yield ['int', '10', 10];
        yield ['int', 10.6, 10];
        yield ['int', 'env(ENV_CASTABLE_STRING_VAR)', 10];
        yield ['int', 'env(ENV_INT_VAL)', 3];

        yield ['float', '10', 10.0];
        yield ['float', IntBackedEnum::TestCase, 1.0];
        yield ['float', StringBackedEnum::NumericCase, 10.5];
        yield ['float', 10, 10.0];
        yield ['float', 10.6, 10.6];
        yield ['float', 'env(ENV_CASTABLE_STRING_VAR)', 10.1];
        yield ['float', 'env(ENV_INT_VAL)', 3.0];

        yield ['null', null, null];

        yield ['bool', false, false];
        yield ['bool', true, true];
        yield ['bool', 'false', false];
        yield ['bool', 'true', true];
        yield ['bool', '0', false];
        yield ['bool', '1', true];

        yield ['false', false, false];
        yield ['false', 'false', false];
        yield ['false', '0', false];

        yield ['true', true, true];
        yield ['true', 'true', true];
        yield ['true', '1', true];

        yield ['array', [1, 2, 3], [1, 2, 3]];
        yield ['array', ['a' => 'a'], ['a' => 'a']];

        $iterator = new class () implements Iterator {
            public function current(): string
            {
                return '';
            }

            public function next(): void
            {
            }

            public function key(): string
            {
                return '';
            }

            public function valid(): bool
            {
                return true;
            }

            public function rewind(): void
            {
            }
        };
        yield ['iterable', ['a' => 'a'], ['a' => 'a']];
        yield ['iterable', $iterator, $iterator];

        yield ['\Traversable', $iterator, $iterator];

        $callback = static fn (): string => 'string';
        yield ['\Closure', $callback, $callback];

        $object = new class {
        };
        $callableClosure = Closure::fromCallable($callback);
        yield ['object', $object, $object];
        yield ['object', $iterator, $iterator];
        yield ['object', $callableClosure, $callableClosure];

        yield ['array', new InstanceOfIterator(AbstractTestCase::class), []];

        yield ['array', new TaggedIterator('tag'), []];

        yield ['\UnitEnum', IteratorFormat::List, IteratorFormat::List];
        yield ['\UnitEnum', StringBackedEnum::TestCase, StringBackedEnum::TestCase];

        yield ['\\'.IteratorFormat::class, IteratorFormat::List, IteratorFormat::List];

        yield ['\BackedEnum', StringBackedEnum::TestCase, StringBackedEnum::TestCase];
        yield ['\\'.StringBackedEnum::class, StringBackedEnum::TestCase, StringBackedEnum::TestCase];

        // - mixed
        yield ['mixed', 'string', 'string'];
        yield ['mixed', 'env(APP_BOUND_VAR)', 'bound_variable_value'];
        yield ['mixed', 'env(ENV_CASTABLE_STRING_VAR)', '10.1'];
        yield ['mixed', 'env(ENV_VARIABLE_REFERENCE)', 'string_additional_string'];
        yield ['mixed', 10, 10];
        yield ['mixed', 10.1, 10.1];
        yield ['mixed', false, false];
        yield ['mixed', true, true];
        yield ['mixed', null, null];
        yield ['mixed', StringBackedEnum::TestCase, StringBackedEnum::TestCase];
        $stringable = new class implements Stringable {
            public function __toString(): string
            {
                return 'string';
            }
        };
        yield [
            'mixed',
            $stringable,
            $stringable,
        ];
        yield ['mixed', [1, 2, 3], [1, 2, 3]];
        yield ['mixed', ['a' => 'a'], ['a' => 'a']];
        yield ['mixed', $callback, $callback];
        $resource = fopen(__FILE__, 'rb');
        yield ['mixed', $resource, $resource];

        $classExtends1 = new ClassExtends1();
        $classExtends2 = new ClassExtends2();
        $classImplements1 = new ClassImplements1();
        $classImplements2 = new ClassImplements2();

        yield ['mixed', $classExtends1, $classExtends1];
        yield ['mixed', $classExtends2, $classExtends2];
        yield ['mixed', $classImplements1, $classImplements1];
        yield ['mixed', $classImplements2, $classImplements2];

        yield ['object', $classExtends1, $classExtends1];
        yield ['object', $classExtends2, $classExtends2];
        yield ['object', $classImplements1, $classImplements1];
        yield ['object', $classImplements2, $classImplements2];

        yield ['\\'.AbstractClass1::class, $classExtends1, $classExtends1];
        yield ['\\'.AbstractClass1::class, $classExtends2, $classExtends2];
        yield ['\\'.ClassExtends1::class, $classExtends1, $classExtends1];
        yield ['\\'.AbstractClass2::class, $classExtends2, $classExtends2];
        yield ['\\'.ClassExtends2::class, $classExtends2, $classExtends2];

        yield ['\\'.Interface1::class, $classImplements1, $classImplements1];
        yield ['\\'.Interface1::class, $classImplements2, $classImplements2];
        yield ['\\'.ClassImplements1::class, $classImplements1, $classImplements1];
        yield ['\\'.Interface2::class, $classImplements2, $classImplements2];
        yield ['\\'.ClassImplements2::class, $classImplements2, $classImplements2];

        // TODO: add more tests on mixed
        // TODO: add more tests on standalone null
        // TODO: add functionality with new Instance
    }

    public static function getDataForNonCompilableBoundVariable(): iterable
    {
        $object = new class {
        };
        $callback = static fn (): string => 'string';

        // variableType, boundValue
        yield ['string', IteratorFormat::List];
        yield [
            'string',
            new class {
            },
        ];
        yield ['string', []];
        yield ['string', $object];
        yield ['string', null];
        yield ['string', $callback];

        yield ['int', 'string'];
        yield ['int', StringBackedEnum::TestCase];
        yield ['int', true];
        yield ['int', []];
        yield ['int', 'env(ENV_STRING_VAL)'];
        yield ['int', $object];
        yield ['int', null];
        yield ['int', $callback];

        yield ['float', 'string'];
        yield ['float', StringBackedEnum::TestCase];
        yield ['float', true];
        yield ['float', []];
        yield ['float', 'env(ENV_STRING_VAL)'];
        yield ['float', $object];
        yield ['float', null];
        yield ['float', $callback];

        yield ['null', 'string'];
        yield ['null', true];
        yield ['null', []];
        yield ['null', 'env(ENV_STRING_VAL)'];
        yield ['null', $object];
        yield ['null', $callback];

        yield ['bool', 'string'];
        yield ['bool', 10];
        yield ['bool', 10.1];
        yield ['bool', []];
        yield ['bool', 'env(ENV_STRING_VAL)'];
        yield ['bool', $object];
        yield ['bool', null];
        yield ['bool', $callback];

        yield ['false', true];
        yield ['false', 'true'];
        yield ['false', '1'];
        yield ['false', null];
        yield ['false', $callback];
        yield ['true', false];
        yield ['true', 'false'];
        yield ['true', '0'];
        yield ['true', null];
        yield ['true', $callback];

        yield ['array', 'string'];
        yield ['array', 10];
        yield ['array', 10.1];
        yield ['array', 'env(ENV_STRING_VAL)'];
        yield ['array', $object];
        yield ['array', null];
        yield ['array', $callback];

        yield ['iterable', 'string'];
        yield ['iterable', 10];
        yield ['iterable', 10.1];
        yield ['iterable', 'env(ENV_STRING_VAL)'];
        yield ['iterable', $object];
        yield ['iterable', null];
        yield ['iterable', 'string'];
        yield ['iterable', $callback];

        yield ['\Traversable', 'string'];
        yield ['\Traversable', 10];
        yield ['\Traversable', 10.1];
        yield ['\Traversable', 'env(ENV_STRING_VAL)'];
        yield ['\Traversable', $object];
        yield ['\Traversable', null];
        yield ['\Traversable', 'string'];
        yield ['\Traversable', []];
        yield ['\Traversable', $callback];

        yield ['\Closure', 'string'];
        yield ['\Closure', 10];
        yield ['\Closure', 10.1];
        yield ['\Closure', 'env(ENV_STRING_VAL)'];
        yield ['\Closure', $object];
        yield ['\Closure', null];
        yield ['\Closure', 'string'];
        yield ['\Closure', []];

        yield ['object', 'string'];
        yield ['object', 10];
        yield ['object', 10.1];
        yield ['object', 'env(ENV_STRING_VAL)'];
        yield ['object', null];
        yield ['object', 'string'];
        yield ['object', []];

        yield ['\UnitEnum', $object];
        yield ['\UnitEnum', $callback];

        yield ['\BackedEnum', IteratorFormat::List];
        yield ['\\'.StringBackedEnum::class, IteratorFormat::List];
        yield ['\\'.IteratorFormat::class, StringBackedEnum::TestCase];
        yield ['\\'.StringBackedEnum::class, $object];
        yield ['\\'.IteratorFormat::class, $object];
        yield ['\\'.StringBackedEnum::class, $callback];
        yield ['\\'.IteratorFormat::class, $callback];

        $classExtends1 = new ClassExtends1();
        $classExtends2 = new ClassExtends2();
        $classImplements1 = new ClassImplements1();
        $classImplements2 = new ClassImplements2();
        $emptyClass = new EmptyClass();

        yield ['\\'.AbstractClass1::class, $emptyClass];
        yield ['\\'.ClassExtends1::class, $emptyClass];
        yield ['\\'.ClassExtends1::class, $classExtends2];
        yield ['\\'.ClassExtends2::class, $classExtends1];
        yield ['\\'.AbstractClass2::class, $emptyClass];
        yield ['\\'.AbstractClass2::class, $classExtends1];

        yield ['\\'.Interface1::class, $emptyClass];
        yield ['\\'.Interface1::class, $classExtends1];
        yield ['\\'.Interface1::class, $classExtends2];
        yield ['\\'.ClassImplements1::class, $classImplements2, $classImplements2];
        yield ['\\'.ClassImplements2::class, $classImplements1, $classImplements1];
        yield ['\\'.Interface2::class, $emptyClass];
        yield ['\\'.Interface2::class, $classExtends1];
        yield ['\\'.Interface2::class, $classExtends2];
        yield ['\\'.Interface2::class, $classImplements1];

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
                        'public readonly int $varOne,',
                        'public readonly string $varTwo,',
                        'public readonly float $varThree,',
                        'public readonly bool $varFour,',
                        'public readonly mixed $varFive,',
                        'public readonly mixed $varSix,',
                        'public readonly mixed $varSeven,',
                        'public readonly mixed $varEight,',
                        'public readonly bool $varNine,',
                        'public readonly bool $varTen,',
                        'public readonly bool $varEleven,',
                        'public readonly bool $varTwelve,',
                        'public readonly bool $varThirteen,',
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: [
                        '$varOne'      => 'env(ENV_CASTABLE_STRING_VAR)',
                        '$varTwo'      => 'env(ENV_CASTABLE_STRING_VAR)',
                        '$varThree'    => 'env(ENV_CASTABLE_STRING_VAR)',
                        '$varFour'     => 'env(ENV_BOOL_VAL)',
                        '$varFive'     => 'env(ENV_FLOAT_VAR)',
                        '$varSix'      => 'env(ENV_BOOL_VAL)',
                        '$varSeven'    => 'env(ENV_INT_VAL)',
                        '$varEight'    => 'env(ENV_STRING_VAL)',
                        '$varNine'     => 'env(ENV_BOOL_VAL)',
                        '$varTen'      => 'env(ENV_VAR_4)',
                        '$varEleven'   => '1',
                        '$varTwelve'   => '0',
                        '$varThirteen' => 'env(ENV_VAR_4)',
                    ],
                ),
            ],
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
    #[DataProvider('getDataForCompilableBoundVariable')]
    public function testCompilesWithClassBoundVariable(
        string $variableType,
        mixed $boundValue,
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
                        "public readonly $variableType \$variable,",
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: [
                        'variable' => $boundValue,
                    ],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);
        self::assertSame($expectedCompiledValue, $class->variable);
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
                        'public readonly \UnitEnum $abstractUnitEnum,',
                        'public readonly \BackedEnum $abstractBackedEnum,',
                        sprintf(
                            'public readonly %s $concreteUnitEnum,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$unitEnum,
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
            globalBoundVariables: [
                '$abstractUnitEnum'   => $unitEnumValue,
                '$abstractBackedEnum' => $backedEnumValue,
            ],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: [
                        '$concreteUnitEnum'   => $unitEnumValue,
                        '$concreteBackedEnum' => $backedEnumValue,
                    ],
                ),
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
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getCallableDataForCompilableBoundVariable')]
    #[DataProvider('getDataForCompilableBoundVariable')]
    public function testCompilesWithFactoryBoundVariable(
        string $variableType,
        mixed $boundValue,
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
                                public function create(%s $variable): %s
                                {
                                    return new %s($variable);
                                }
                                BODY,
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
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    factory: new Factory(
                        self::GENERATED_CLASS_NAMESPACE.$className2,
                        method: 'create',
                        boundedVariables: [
                            'variable' => $boundValue,
                        ],
                    ),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class);
        self::assertSame($expectedCompiledValue, $class->variable);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getDataForCompilableBoundVariable')]
    public function testCompilesWithGlobalBoundVariable(
        string $variableType,
        mixed $boundValue,
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
                        "public readonly $variableType \$variable,",
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
            globalBoundVariables: [
                'variable' => $boundValue,
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);
        self::assertSame($expectedCompiledValue, $class->variable);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithGlobalBoundVariableOverwrittenByClassBind(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $variable,',
                    ]),
            )
            ->generate();

        $classes = [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'];

        $config = $this->generateConfig(
            includedPaths: $classes,
            globalBoundVariables: [
                '$variable' => 'globalVariableValue',
            ],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: ['$variable' => 'localVariableValue'],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);
        self::assertEquals('localVariableValue', $class->variable);
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
                        'public readonly int $variable = 5,',
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: ['$variable' => 'env(ENV_SOME_NON_EXISTING_VARIABLE)'],
                ),
            ],
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
                    ->setConstructorArguments(['public readonly string $arg',]),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];

        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: ['$arg' => 'env(ENV_VAR_1)env(ENV_VAR_2)_env(ENV_VAR_3)-TEST-env(ENV_VAR_4)'],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $fullClassNameSpace = self::GENERATED_CLASS_NAMESPACE.$className;
        $class = $container->get($fullClassNameSpace);

        self::assertIsObject($class);
        self::assertInstanceOf($fullClassNameSpace, $class);
        self::assertEquals('test_one10.1_test-three-TEST-true', $class->arg);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithMultipleInterfaceReferences(): void
    {
        $interface1 = ClassGenerator::getClassName();
        $interface2 = ClassGenerator::getClassName();
        $interface3 = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface1.php")
                    ->setName($interface1)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface2.php")
                    ->setName($interface2)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface3.php")
                    ->setName($interface3)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface2]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'test'),
                        'public readonly string $dep0,',
                        sprintf(
                            'public readonly %s $dep1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1,
                        ),
                        sprintf(
                            'public readonly %s $dep2,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface2,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface3]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className4.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interface3.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interface1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interface2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interface3 => self::GENERATED_CLASS_NAMESPACE.$className4,
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        self::assertEquals('test', $class->dep0);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface1, $class->dep1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class->dep1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface2, $class->dep2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class->dep2);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$interface3,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$interface3),
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className4,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$interface3),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getCallableDataForCompilableBoundVariable')]
    #[DataProvider('getDataForCompilableBoundVariable')]
    public function testCompilesWithRequiredMethodBoundVariable(
        string $variableType,
        mixed $boundValue,
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
                        sprintf(
                            <<<'BODY'
                                public function required(%s $variable): void
                                {
                                    $this->variable = $variable;
                                }
                                BODY,
                            $variableType,
                        ),
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            ],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    variableBindings: [
                        'variable' => $boundValue,
                    ],
                    requiredMethodCalls: ['required'],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class);
        $this->assertInitialized($class, 'variable');
        self::assertSame($expectedCompiledValue, $class->variable);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getDataForNonCompilableBoundVariable')]
    public function testDoesNotCompileWithClassBoundVariable(
        string $variableType,
        mixed $boundValue,
    ): void {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        "public readonly $variableType \$variable,",
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: [
                        'variable' => $boundValue,
                    ],
                ),
            ],
        );

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "variable::%s" as bound expression has incompatible type "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
                ltrim($variableType, '\\'),
                get_debug_type($boundValue),
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getCallableDataForNonCompilableBoundVariable')]
    #[DataProvider('getDataForNonCompilableBoundVariable')]
    public function testDoesNotCompileWithFactoryBoundVariable(
        string $variableType,
        mixed $boundValue,
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
                                public function create(%s $variable): %s
                                {
                                    return new %s($variable);
                                }
                                BODY,
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
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    factory: new Factory(
                        self::GENERATED_CLASS_NAMESPACE.$className2,
                        method: 'create',
                        boundedVariables: [
                            'variable' => $boundValue,
                        ],
                    ),
                ),
            ],
        );

        // TODO: change exception message
        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "variable::%s" as bound expression has incompatible type "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className2,
                ltrim($variableType, '\\'),
                get_debug_type($boundValue),
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getDataForNonCompilableBoundVariable')]
    public function testDoesNotCompileWithGlobalBoundVariable(
        string $variableType,
        mixed $boundValue,
    ): void {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        "public readonly $variableType \$variable,",
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
            globalBoundVariables: [
                'variable' => $boundValue,
            ],
        );

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "variable::%s" as bound expression has incompatible type "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
                ltrim($variableType, '\\'),
                get_debug_type($boundValue),
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getDataForNonCompilableBoundVariable')]
    public function testDoesNotCompileWithRequiredMethodBoundVariable(
        string $variableType,
        mixed $boundValue,
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
                        sprintf(
                            <<<'BODY'
                                public function required(%s $variable): void
                                {
                                    $this->variable = $variable;
                                }
                                BODY,
                            $variableType,
                        ),
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            ],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    variableBindings: [
                        'variable' => $boundValue,
                    ],
                    requiredMethodCalls: ['required'],
                ),
            ],
        );

        // TODO: change exception message
        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "variable::%s" as bound expression has incompatible type "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className1,
                ltrim($variableType, '\\'),
                get_debug_type($boundValue),
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }
}

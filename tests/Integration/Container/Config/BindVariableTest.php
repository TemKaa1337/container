<?php

declare(strict_types=1);

namespace Container\Config;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
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
    public function testCompileWithCastedBoundVariablesFromConfig(): void
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
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: [
                        '$varOne'   => 'env(ENV_CASTABLE_STRING_VAR)',
                        '$varTwo'   => 'env(ENV_CASTABLE_STRING_VAR)',
                        '$varThree' => 'env(ENV_CASTABLE_STRING_VAR)',
                        '$varFour'  => 'env(ENV_CASTABLE_STRING_VAR)',
                        '$varFive'  => 'env(ENV_FLOAT_VAR)',
                        '$varSix'   => 'env(ENV_BOOL_VAL)',
                        '$varSeven' => 'env(ENV_INT_VAL)',
                        '$varEight' => 'env(ENV_STRING_VAL)',
                        '$varNine'  => 'env(ENV_BOOL_VAL)',
                        '$varTen'   => 'env(ENV_VAR_4)',
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
    public function testCompilesWithCastedBoundVariablesFromConfig(): void
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
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: [
                        '$varOne'   => 'env(ENV_CASTABLE_STRING_VAR)',
                        'varTwo'    => 'env(ENV_CASTABLE_STRING_VAR)',
                        '$varThree' => 'env(ENV_CASTABLE_STRING_VAR)',
                        'varFour'   => 'env(ENV_CASTABLE_STRING_VAR)',
                        '$varFive'  => 'env(ENV_FLOAT_VAR)',
                        'varSix'    => 'env(ENV_BOOL_VAL)',
                        '$varSeven' => 'env(ENV_INT_VAL)',
                        'varEight'  => 'env(ENV_STRING_VAL)',
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
    public function testCompilesWithCastingStringsFromConfig(): void
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
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: [
                        '$varOne'   => '10.1',
                        'varTwo'    => '10.1',
                        '$varThree' => '10.1',
                        'varFour'   => '10.1',
                        '$varFive'  => '10.1',
                        'varSix'    => 'false',
                        '$varSeven' => '3',
                        'varEight'  => 'string',
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
    public function testCompilesWithEnumsFromConfig(): void
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
    public function testCompilesWithGlobalBoundVariable(): void
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
                '$variable' => 'variableValue',
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);
        self::assertEquals('variableValue', $class->variable);
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
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithMultipleEnvVarsInSingleBoundVariableFromConfig(): void
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
}

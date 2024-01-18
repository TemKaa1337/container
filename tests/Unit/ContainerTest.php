<?php

declare(strict_types=1);

namespace Tests\Unit;

use Closure;
use Generator;
use Psr\Container\ContainerExceptionInterface;
use ReflectionAttribute;
use ReflectionClass;
use Temkaa\SimpleContainer\Container;
use Temkaa\SimpleContainer\Definition\Definition;
use Temkaa\SimpleContainer\Exception\CircularReferenceException;
use Temkaa\SimpleContainer\Exception\Config\InvalidPathException;
use Temkaa\SimpleContainer\Exception\EntryNotFoundException;
use Temkaa\SimpleContainer\Exception\NonAutowirableClassException;
use Temkaa\SimpleContainer\Exception\UninstantiableEntryException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Exception\UnsupportedCastTypeException;
use function sprintf;

// TODO: FUTURE
// TODO: add tagged iterator by tag test to concrete collection class collection from config
// TODO: add tagged iterator by tag test to concrete collection class collection from attribute
// TODO: add config decorator test (only by interface?)
// TODO: add attribute decorator test (only by interface?)
// TODO: add singleton/not singleton test
// TODO: add caching

// TODO: NOW
// TODO: add tests on binding string/env vars with attributes
final class ContainerTest extends AbstractUnitTestCase
{
    public static function getDataForCompilesWithUninstantiableEntryTest(): iterable
    {
        yield ['TestClass'.self::getNextGeneratedClassNumber(), 'abstract class', [], 'public'];

        yield ['TestClass'.self::getNextGeneratedClassNumber(), 'final class', [], 'private'];

        yield ['TestClass'.self::getNextGeneratedClassNumber(), 'final class', [], 'protected'];

        yield [
            'TestClass'.self::getNextGeneratedClassNumber(),
            'final class',
            [self::ATTRIBUTE_NON_AUTOWIRABLE_SIGNATURE],
            'public',
        ];
    }

    public static function getDataForDoesNotCompileDueToInternalClassDependencyTest(): iterable
    {
        yield [
            'TestClass'.self::getNextGeneratedClassNumber(),
            'public readonly \Closure $generator',
            Closure::class,
        ];
        yield [
            'TestClass'.self::getNextGeneratedClassNumber(),
            'public readonly \Generator $generator',
            Generator::class,
        ];
        yield [
            'TestClass'.self::getNextGeneratedClassNumber(),
            'public readonly \ReflectionClass $r',
            ReflectionClass::class,
        ];
        yield [
            'TestClass'.self::getNextGeneratedClassNumber(),
            'public readonly \ReflectionAttribute $r',
            ReflectionAttribute::class,
        ];
    }

    public static function getDataForDoesNotCompileDueToNotDeterminedArgumentTypeTest(): iterable
    {
        yield [
            $className = 'TestClass'.self::getNextGeneratedClassNumber(),
            'public readonly array|string $arg',
            sprintf(
                'Cannot resolve argument "arg" with union type "array|string" in class "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
            ),
        ];

        yield [
            $className = 'TestClass'.self::getNextGeneratedClassNumber(),
            'public readonly array|object $arg',
            sprintf(
                'Cannot resolve argument "arg" with union type "object|array" in class "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
            ),
        ];

        yield [
            $className = 'TestClass'.self::getNextGeneratedClassNumber(),
            'public readonly \Generator&\Iterator $arg',
            sprintf(
                'Cannot resolve argument "arg" with intersection type "Generator&Iterator" in class "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
            ),
        ];

        yield [
            $className = 'TestClass'.self::getNextGeneratedClassNumber(),
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
            'TestClass'.self::getNextGeneratedClassNumber(),
            ['public readonly object $arg'],
            UnsupportedCastTypeException::class,
            sprintf('Cannot cast value of type "%s" to "%s".', 'string', 'object'),
        ];

        yield [
            'TestClass'.self::getNextGeneratedClassNumber(),
            ['public readonly array $arg'],
            UnsupportedCastTypeException::class,
            sprintf('Cannot cast value of type "%s" to "%s".', 'string', 'array'),
        ];

        yield [
            'TestClass'.self::getNextGeneratedClassNumber(),
            ['public readonly iterable $arg'],
            UnsupportedCastTypeException::class,
            sprintf('Cannot cast value of type "%s" to "%s".', 'string', 'iterable'),
        ];

        yield [
            'TestClass'.self::getNextGeneratedClassNumber(),
            ['public readonly int $arg'],
            UnsupportedCastTypeException::class,
            sprintf('Cannot cast value of type "%s" to "%s".', 'string', 'int'),
        ];

        yield [
            'TestClass'.self::getNextGeneratedClassNumber(),
            ['public readonly float $arg'],
            UnsupportedCastTypeException::class,
            sprintf('Cannot cast value of type "%s" to "%s".', 'string', 'float'),
        ];
    }

    public static function getDataForDoesNotCompileWithUninstantiableEntryTest(): iterable
    {
        yield [
            'TestClass'.self::getNextGeneratedClassNumber(),
            $invalidClassName = 'TestClass'.self::getNextGeneratedClassNumber(),
            'abstract class',
            'public',
            [sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$invalidClassName)],
        ];

        yield [
            'TestClass'.self::getNextGeneratedClassNumber(),
            $invalidClassName = 'TestClass'.self::getNextGeneratedClassNumber(),
            'final class',
            'protected',
            [sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$invalidClassName)],
        ];

        yield [
            'TestClass'.self::getNextGeneratedClassNumber(),
            $invalidClassName = 'TestClass'.self::getNextGeneratedClassNumber(),
            'final class',
            'private',
            [sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$invalidClassName)],
        ];
    }

    public function testCompileClassWithBuiltInTypedArgument(): void
    {
        $classPath = 'TestClass'.self::getNextGeneratedClassNumber();

        $files = [self::GENERATED_CLASS_STUB_PATH."$classPath.php"];
        $config = $this->getConfig(services: ['include' => $files]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classPath.php",
            className: $classPath,
            hasConstructor: true,
            constructorArguments: ['public readonly int $age,'],
        );

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "%s::%s".',
                self::GENERATED_CLASS_NAMESPACE.$classPath,
                'age',
                'int',
            ),
        );

        $container = new Container($config);
        $container->compile();
    }

    public function testCompileClassWithCircularDependencies(): void
    {
        $circularClassName1 = 'TestClass'.self::getNextGeneratedClassNumber();
        $circularClassName2 = 'TestClass'.self::getNextGeneratedClassNumber();

        $files = [self::GENERATED_CLASS_STUB_PATH."$circularClassName1.php"];
        $config = $this->getConfig(services: ['include' => $files]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$circularClassName1.php",
            className: $circularClassName1,
            hasConstructor: true,
            constructorArguments: [
                sprintf('public readonly %s $arg', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$circularClassName2),
            ],
        );

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$circularClassName2.php",
            className: $circularClassName2,
            hasConstructor: true,
            constructorArguments: [
                sprintf('public readonly %s $arg', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$circularClassName1),
            ],
        );

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(CircularReferenceException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate class "%s" as it has circular references "%s".',
                $circularClassName1,
                "$circularClassName1 -> $circularClassName2",
            ),
        );

        $container = new Container($config);
        $container->compile();
    }

    public function testCompileClassWithNonTypedArgument(): void
    {
        $classPath = 'TestClass'.self::getNextGeneratedClassNumber();

        $files = [self::GENERATED_CLASS_STUB_PATH."$classPath.php"];
        $config = $this->getConfig(services: ['include' => $files]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classPath.php",
            className: $classPath,
            hasConstructor: true,
            constructorArguments: ['$arg'],
        );

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(UninstantiableEntryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry with non-typed parameters "%s" -> "%s".',
                self::GENERATED_CLASS_NAMESPACE.$classPath,
                'arg',
            ),
        );

        $container = new Container($config);
        $container->compile();
    }

    public function testCompileClassWithTypeHintedEnum(): void
    {
        $collectorClassName = 'TestClass'.self::getNextGeneratedClassNumber();
        $enumClassName = 'TestClass'.self::getNextGeneratedClassNumber();

        $files = [self::GENERATED_CLASS_STUB_PATH."$collectorClassName.php"];
        $config = $this->getConfig(services: ['include' => $files]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php",
            className: $collectorClassName,
            hasConstructor: true,
            constructorArguments: [
                sprintf('public readonly %s $arg', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$enumClassName),
            ],
        );

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$enumClassName.php",
            className: $enumClassName,
            classNamePrefix: 'enum',
        );

        $this->expectException(UninstantiableEntryException::class);
        $this->expectExceptionMessage(
            sprintf('Cannot instantiate entry with id "%s".', self::GENERATED_CLASS_NAMESPACE.$enumClassName),
        );

        $container = new Container($config);
        $container->compile();
    }

    public function testCompileClassWithoutDependencies(): void
    {
        $classPath = 'TestClass'.self::getNextGeneratedClassNumber();

        $files = [self::GENERATED_CLASS_STUB_PATH."$classPath.php"];
        $config = $this->getConfig(services: ['include' => $files]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classPath.php",
            className: $classPath,
        );

        $container = new Container($config);
        $container->compile();

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$classPath);

        self::assertIsObject($object);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classPath, $object);
    }

    public function testCompileWithCastedBoundVariablesFromAttributes(): void
    {
        $classPath = 'TestClass'.self::getNextGeneratedClassNumber();

        $config = $this->getConfig(services: ['include' => [self::GENERATED_CLASS_STUB_PATH.$classPath.'.php']]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classPath.php",
            className: $classPath,
            hasConstructor: true,
            constructorArguments: [
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                'public readonly int $varOne,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                'public readonly string $varTwo,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                'public readonly float $varThree,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                'public readonly bool $varFour,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_FLOAT_VAR)'),
                'public readonly mixed $varFive,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_BOOL_VAL)'),
                'public readonly mixed $varSix,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_INT_VAL)'),
                'public readonly mixed $varSeven,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_STRING_VAL)'),
                'public readonly mixed $varEight,',
            ],
        );

        $env = [
            'ENV_CASTABLE_STRING_VAR' => '10.1',
            'ENV_FLOAT_VAR'           => '10.1',
            'ENV_BOOL_VAL'            => 'false',
            'ENV_INT_VAL'             => '3',
            'ENV_STRING_VAL'          => 'string',
        ];

        $container = new Container($config, $env);
        $container->compile();

        self::assertIsObject($container);
        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$classPath);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classPath, $class);

        self::assertEquals(10, $class->varOne);
        self::assertEquals('10.1', $class->varTwo);
        self::assertEquals(10.1, $class->varThree);
        self::assertTrue($class->varFour);
        self::assertEquals('10.1', $class->varFive);
        self::assertEquals('false', $class->varSix);
        self::assertEquals('3', $class->varSeven);
        self::assertEquals('string', $class->varEight);
    }

    public function testCompileWithCastedBoundVariablesFromConfig(): void
    {
        $classPath = 'TestClass'.self::getNextGeneratedClassNumber();

        $config = $this->getConfig(
            services: ['include' => [self::GENERATED_CLASS_STUB_PATH.$classPath.'.php']],
            classBindings: [
                self::GENERATED_CLASS_NAMESPACE.$classPath => [
                    'bind' => [
                        '$varOne'   => 'env(ENV_CASTABLE_STRING_VAR)',
                        'varTwo'    => 'env(ENV_CASTABLE_STRING_VAR)',
                        '$varThree' => 'env(ENV_CASTABLE_STRING_VAR)',
                        'varFour'   => 'env(ENV_CASTABLE_STRING_VAR)',
                        '$varFive'  => 'env(ENV_FLOAT_VAR)',
                        'varSix'    => 'env(ENV_BOOL_VAL)',
                        '$varSeven' => 'env(ENV_INT_VAL)',
                        'varEight'  => 'env(ENV_STRING_VAL)',
                    ],
                ],
            ],
        );

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classPath.php",
            className: $classPath,
            hasConstructor: true,
            constructorArguments: [
                'public readonly int $varOne,',
                'public readonly string $varTwo,',
                'public readonly float $varThree,',
                'public readonly bool $varFour,',
                'public readonly mixed $varFive,',
                'public readonly mixed $varSix,',
                'public readonly mixed $varSeven,',
                'public readonly mixed $varEight,',
            ],
        );

        $env = [
            'ENV_CASTABLE_STRING_VAR' => '10.1',
            'ENV_FLOAT_VAR'           => '10.1',
            'ENV_BOOL_VAL'            => 'false',
            'ENV_INT_VAL'             => '3',
            'ENV_STRING_VAL'          => 'string',
        ];

        $container = new Container($config, $env);
        $container->compile();

        self::assertIsObject($container);
        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$classPath);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classPath, $class);

        self::assertEquals(10, $class->varOne);
        self::assertEquals('10.1', $class->varTwo);
        self::assertEquals(10.1, $class->varThree);
        self::assertTrue($class->varFour);
        self::assertEquals('10.1', $class->varFive);
        self::assertEquals('false', $class->varSix);
        self::assertEquals('3', $class->varSeven);
        self::assertEquals('string', $class->varEight);
    }

    public function testCompileWithNonExistentClass(): void
    {
        $classPath = self::GENERATED_CLASS_STUB_PATH.'NonExistentClass.php';
        $config = $this->getConfig(services: ['include' => [$classPath]]);

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('The specified path "'.$classPath.'" does not exist.');

        $container = new Container($config);
        $container->compile();
    }

    public function testCompilesWithCastingStringsFromAttribute(): void
    {
        $classPath = 'TestClass'.self::getNextGeneratedClassNumber();

        $config = $this->getConfig(services: ['include' => [self::GENERATED_CLASS_STUB_PATH.$classPath.'.php']]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classPath.php",
            className: $classPath,
            hasConstructor: true,
            constructorArguments: [
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, '10.1'),
                'public readonly int $varOne,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, '10.1'),
                'public readonly string $varTwo,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, '10.1'),
                'public readonly float $varThree,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, '10.1'),
                'public readonly bool $varFour,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, '10.1'),
                'public readonly mixed $varFive,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'false'),
                'public readonly mixed $varSix,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, '3'),
                'public readonly mixed $varSeven,',
                sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'string'),
                'public readonly mixed $varEight,',
            ],
        );

        $container = new Container($config, env: []);
        $container->compile();

        self::assertIsObject($container);
        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$classPath);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classPath, $class);

        self::assertEquals(10, $class->varOne);
        self::assertEquals('10.1', $class->varTwo);
        self::assertEquals(10.1, $class->varThree);
        self::assertTrue($class->varFour);
        self::assertEquals('10.1', $class->varFive);
        self::assertEquals('false', $class->varSix);
        self::assertEquals('3', $class->varSeven);
        self::assertEquals('string', $class->varEight);
    }

    public function testCompilesWithCastingStringsFromConfig(): void
    {
        $classPath = 'TestClass'.self::getNextGeneratedClassNumber();

        $config = $this->getConfig(
            services: ['include' => [self::GENERATED_CLASS_STUB_PATH.$classPath.'.php']],
            classBindings: [
                self::GENERATED_CLASS_NAMESPACE.$classPath => [
                    'bind' => [
                        '$varOne'   => '10.1',
                        'varTwo'    => '10.1',
                        '$varThree' => '10.1',
                        'varFour'   => '10.1',
                        '$varFive'  => '10.1',
                        'varSix'    => 'false',
                        '$varSeven' => '3',
                        'varEight'  => 'string',
                    ],
                ],
            ],
        );

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classPath.php",
            className: $classPath,
            hasConstructor: true,
            constructorArguments: [
                'public readonly int $varOne,',
                'public readonly string $varTwo,',
                'public readonly float $varThree,',
                'public readonly bool $varFour,',
                'public readonly mixed $varFive,',
                'public readonly mixed $varSix,',
                'public readonly mixed $varSeven,',
                'public readonly mixed $varEight,',
            ],
        );

        $container = new Container($config, env: []);
        $container->compile();

        self::assertIsObject($container);
        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$classPath);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classPath, $class);

        self::assertEquals(10, $class->varOne);
        self::assertEquals('10.1', $class->varTwo);
        self::assertEquals(10.1, $class->varThree);
        self::assertTrue($class->varFour);
        self::assertEquals('10.1', $class->varFive);
        self::assertEquals('false', $class->varSix);
        self::assertEquals('3', $class->varSeven);
        self::assertEquals('string', $class->varEight);
    }

    public function testCompilesWithClassAliases(): void
    {
        $classWithAliases = 'TestClass'.self::getNextGeneratedClassNumber();
        $classWithAliasesNamespace = self::GENERATED_CLASS_NAMESPACE.$classWithAliases;
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classWithAliases.php",
            className: $classWithAliases,
            attributes: [
                sprintf(self::ATTRIBUTE_ALIAS_SIGNATURE, 'empty_2'),
                sprintf(self::ATTRIBUTE_ALIAS_SIGNATURE, 'empty2'),
            ],
        );
        $config = $this->getConfig(services: ['include' => [self::GENERATED_CLASS_STUB_PATH.$classWithAliases.'.php']]);

        $container = new Container($config);
        $container->compile();

        self::assertIsObject($container);

        $class = $container->get($classWithAliasesNamespace);

        self::assertInstanceOf($classWithAliasesNamespace, $class);
        self::assertTrue($container->has('empty_2'));
        self::assertTrue($container->has('empty2'));
        self::assertSame($class, $container->get('empty_2'));
        self::assertSame($class, $container->get('empty2'));
    }

    public function testCompilesWithExplicitDependencySetting(): void
    {
        $collectorName = 'TestClass'.self::getNextGeneratedClassNumber();
        $className1 = 'TestClass'.self::getNextGeneratedClassNumber();
        $className2 = 'TestClass'.self::getNextGeneratedClassNumber();
        $className3 = 'TestClass'.self::getNextGeneratedClassNumber();

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php",
            className: $className1,
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php",
            className: $className2,
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php",
            className: $className3,
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorName.php",
            className: $collectorName,
            hasConstructor: true,
            constructorArguments: [
                sprintf('public readonly %s $dependency1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1),
                sprintf('public readonly %s $dependency2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                sprintf('public readonly %s $dependency3,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
            ],
        );

        $files = [
            self::GENERATED_CLASS_STUB_PATH.$collectorName.'.php',
            self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
        ];
        $config = $this->getConfig(services: ['include' => $files]);

        $container = new Container($config);
        $container->compile();

        $this->assertIsObject($container);

        $this->assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className1));
        $this->assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
        );

        $this->assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className2));
        $this->assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
        );

        $this->assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className3));
        $this->assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className3,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
        );

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorName);
        self::assertIsObject($object->dependency1);
        self::assertIsObject($object->dependency2);
        self::assertIsObject($object->dependency3);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $object->dependency1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $object->dependency2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $object->dependency3);
    }

    public function testCompilesWithInterfaceBindingByClass(): void
    {
        $className = 'TestClass'.self::getNextGeneratedClassNumber();
        $interfaceName = 'Interface'.self::getNextGeneratedClassNumber();

        $classPaths = [
            self::GENERATED_CLASS_STUB_PATH.$className.'.php',
            self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
        ];
        $config = $this->getConfig(
            services: ['include' => $classPaths],
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName => self::GENERATED_CLASS_NAMESPACE.$className,
            ],
        );

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php",
            className: $className,
            interfacesImplements: [self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName],
        );

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php",
            className: $interfaceName,
            classNamePrefix: 'interface',
        );

        $container = new Container($config);
        $container->compile();

        self::assertIsObject($container);

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interfaceName, $class);
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$interfaceName));
        self::assertSame($class, $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName));
    }

    public function testCompilesWithInterfaceTagInheritance(): void
    {
        $interfaceName1 = 'TestClass'.self::getNextGeneratedClassNumber();
        $interfaceAbsoluteNamespace1 = self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1;
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName1.php",
            className: $interfaceName1,
            attributes: [sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'interface1')],
            classNamePrefix: 'interface',
        );
        $interfaceName2 = 'TestClass'.self::getNextGeneratedClassNumber();
        $interfaceAbsoluteNamespace2 = self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2;
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName2.php",
            className: $interfaceName2,
            attributes: [sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'interface2')],
            classNamePrefix: 'interface',
        );
        $interfaceName3 = 'TestClass'.self::getNextGeneratedClassNumber();
        $interfaceAbsoluteNamespace3 = self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName3;
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName3.php",
            className: $interfaceName3,
            attributes: [sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'interface3')],
            extends: [$interfaceAbsoluteNamespace1, $interfaceAbsoluteNamespace2],
            classNamePrefix: 'interface',
        );
        $classImplementsName = 'TestClass'.self::getNextGeneratedClassNumber();
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classImplementsName.php",
            className: $classImplementsName,
            attributes: [sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'class1')],
            interfacesImplements: [$interfaceAbsoluteNamespace3],
        );

        $classes = [
            self::GENERATED_CLASS_STUB_PATH.$classImplementsName.'.php',
            self::GENERATED_CLASS_STUB_PATH.$interfaceName3.'.php',
            self::GENERATED_CLASS_STUB_PATH.$interfaceName1.'.php',
            self::GENERATED_CLASS_STUB_PATH.$interfaceName2.'.php',
        ];
        $config = $this->getConfig(services: ['include' => $classes]);

        $container = new Container($config);
        $container->compile();

        $r = new ReflectionClass($container);
        $definitions = $r->getProperty('definitions')->getValue($container);

        /** @var Definition $classDefinition */
        $classDefinition = $definitions[self::GENERATED_CLASS_NAMESPACE.$classImplementsName];

        self::assertEqualsCanonicalizing(
            [
                'class1',
                'interface3',
                'interface1',
                'interface2',
            ],
            $classDefinition->getTags(),
        );
    }

    public function testCompilesWithMultipleEnvVarsInSingleBoundVariableFromAttribute(): void
    {
        $className = 'TestClass'.self::getNextGeneratedClassNumber();
        $files = [self::GENERATED_CLASS_STUB_PATH."$className.php"];

        $config = $this->getConfig(services: ['include' => $files]);

        $env = ['ENV_VAR_1' => 'test_one', 'ENV_VAR_2' => '10.1', 'ENV_VAR_3' => 'test-three', 'ENV_VAR_4' => 'true'];

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php",
            className: $className,
            hasConstructor: true,
            constructorArguments: [
                sprintf(
                    self::ATTRIBUTE_PARAMETER_SIGNATURE,
                    'env(ENV_VAR_1)env(ENV_VAR_2)_env(ENV_VAR_3)-TEST-env(ENV_VAR_4)',
                ),
                'public readonly string $arg,',
            ],
        );

        $container = new Container($config, $env);
        $container->compile();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);
        self::assertEquals('test_one10.1_test-three-TEST-true', $class->arg);
    }

    public function testCompilesWithMultipleEnvVarsInSingleBoundVariableFromConfig(): void
    {
        $className = 'TestClass'.self::getNextGeneratedClassNumber();
        $files = [self::GENERATED_CLASS_STUB_PATH."$className.php"];

        $config = $this->getConfig(
            services: ['include' => $files],
            classBindings: [
                self::GENERATED_CLASS_NAMESPACE.$className => [
                    'bind' => ['$arg' => 'env(ENV_VAR_1)env(ENV_VAR_2)_env(ENV_VAR_3)-TEST-env(ENV_VAR_4)'],
                ],
            ],
        );

        $env = ['ENV_VAR_1' => 'test_one', 'ENV_VAR_2' => '10.1', 'ENV_VAR_3' => 'test-three', 'ENV_VAR_4' => 'true'];

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php",
            className: $className,
            hasConstructor: true,
            constructorArguments: [
                'public readonly string $arg',
            ],
        );

        $container = new Container($config, $env);
        $container->compile();

        $fullClassNameSpace = self::GENERATED_CLASS_NAMESPACE.$className;
        $class = $container->get($fullClassNameSpace);

        self::assertIsObject($class);
        self::assertInstanceOf($fullClassNameSpace, $class);
        self::assertEquals('test_one10.1_test-three-TEST-true', $class->arg);
    }

    public function testCompilesWithTaggedInterfaceImplementation(): void
    {
        $collectorClassName = 'TestClass'.self::getNextGeneratedClassNumber();
        $interfaceName = 'TestClass'.self::getNextGeneratedClassNumber();
        $classImplementing1 = 'TestClass'.self::getNextGeneratedClassNumber();
        $classImplementing2 = 'TestClass'.self::getNextGeneratedClassNumber();

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php",
            className: $interfaceName,
            attributes: [sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'Interface1')],
            classNamePrefix: 'interface',
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classImplementing1.php",
            className: $classImplementing1,
            interfacesImplements: [self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName],
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classImplementing2.php",
            className: $classImplementing2,
            interfacesImplements: [self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName],
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php",
            className: $collectorClassName,
            hasConstructor: true,
            constructorArguments: [
                sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'Interface1'),
                'public readonly iterable $dependency,',
            ],
        );

        $classes = [
            self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
            self::GENERATED_CLASS_STUB_PATH.$classImplementing1.'.php',
            self::GENERATED_CLASS_STUB_PATH.$classImplementing2.'.php',
        ];
        $config = $this->getConfig(services: ['include' => $classes]);

        $container = new Container($config);
        $container->compile();

        $this->assertIsObject($container);

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorClassName, $class);
        self::assertCount(2, $class->dependency);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classImplementing1, $class->dependency[0]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classImplementing2, $class->dependency[1]);
    }

    public function testCompilesWithTaggedIteratorFromAttribute(): void
    {
        $collectorName = 'TestClass'.self::getNextGeneratedClassNumber();
        $taggedClassName = 'TestClass'.self::getNextGeneratedClassNumber();

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorName.php",
            className: $collectorName,
            hasConstructor: true,
            constructorArguments: [
                sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'tag_1'),
                'public readonly array $dependency1,',
                sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'tag_1'),
                'public readonly iterable $dependency2,',
            ],
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$taggedClassName.php",
            className: $taggedClassName,
            attributes: [sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'tag_1')],
        );

        $classes = [
            self::GENERATED_CLASS_STUB_PATH.$collectorName.'.php',
            self::GENERATED_CLASS_STUB_PATH.$taggedClassName.'.php',
        ];
        $config = $this->getConfig(services: ['include' => $classes]);

        $container = new Container($config);
        $container->compile();

        $this->assertIsObject($container);

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorName, $class);
        self::assertIsArray($class->dependency1);
        self::assertIsArray($class->dependency2);

        self::assertCount(1, $class->dependency1);
        self::assertCount(1, $class->dependency2);

        self::assertIsObject($class->dependency1[0]);
        self::assertIsObject($class->dependency2[0]);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$taggedClassName, $class->dependency1[0]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$taggedClassName, $class->dependency2[0]);
    }

    public function testCompilesWithTaggedIteratorFromConfig(): void
    {
        $collectorName = 'TestClass'.self::getNextGeneratedClassNumber();
        $taggedClassName = 'TestClass'.self::getNextGeneratedClassNumber();

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorName.php",
            className: $collectorName,
            hasConstructor: true,
            constructorArguments: [
                'public readonly array $dependency1,',
                'public readonly iterable $dependency2,',
            ],
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$taggedClassName.php",
            className: $taggedClassName,
        );

        $classes = [
            self::GENERATED_CLASS_STUB_PATH.$collectorName.'.php',
            self::GENERATED_CLASS_STUB_PATH.$taggedClassName.'.php',
        ];
        $config = $this->getConfig(
            services: ['include' => $classes],
            classBindings: [
                self::GENERATED_CLASS_NAMESPACE.$collectorName   => [
                    'bind' => [
                        'dependency1' => '!tagged empty_2',
                        'dependency2' => '!tagged empty_2',
                    ],
                ],
                self::GENERATED_CLASS_NAMESPACE.$taggedClassName => [
                    'tags' => ['empty_2'],
                ],
            ],
        );

        $container = new Container($config);
        $container->compile();

        $this->assertIsObject($container);

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorName, $class);
        self::assertIsArray($class->dependency1);
        self::assertIsArray($class->dependency2);

        self::assertCount(1, $class->dependency1);
        self::assertCount(1, $class->dependency2);

        self::assertIsObject($class->dependency1[0]);
        self::assertIsObject($class->dependency2[0]);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$taggedClassName, $class->dependency1[0]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$taggedClassName, $class->dependency2[0]);
    }

    /**
     * @dataProvider getDataForCompilesWithUninstantiableEntryTest
     */
    public function testCompilesWithUninstantiableEntry(
        string $className,
        string $classNamePrefix,
        array $attributes,
        string $constructorVisibility,
    ): void {
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php",
            className: $className,
            attributes: $attributes,
            classNamePrefix: $classNamePrefix,
            constructorVisibility: $constructorVisibility,
            hasConstructor: true,
        );

        $classPath = self::GENERATED_CLASS_STUB_PATH.$className.'.php';
        $config = $this->getConfig(services: ['include' => [$classPath]]);

        $container = new Container($config);
        $container->compile();

        self::assertIsObject($container);
        self::assertFalse($container->has($className));
    }

    public function testCompilesWithoutSettingAllDependenciesClassWithDependencies(): void
    {
        $collectorName = 'TestClass'.self::getNextGeneratedClassNumber();
        $className1 = 'TestClass'.self::getNextGeneratedClassNumber();
        $className2 = 'TestClass'.self::getNextGeneratedClassNumber();
        $className3 = 'TestClass'.self::getNextGeneratedClassNumber();

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php",
            className: $className1,
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php",
            className: $className2,
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php",
            className: $className3,
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorName.php",
            className: $collectorName,
            hasConstructor: true,
            constructorArguments: [
                sprintf('public readonly %s $dependency1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1),
                sprintf('public readonly %s $dependency2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                sprintf('public readonly %s $dependency3,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
            ],
        );

        $files = [
            self::GENERATED_CLASS_STUB_PATH.$collectorName.'.php',
        ];
        $config = $this->getConfig(services: ['include' => $files]);

        $container = new Container($config);
        $container->compile();

        $this->assertIsObject($container);

        $this->assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className1));
        $this->assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
        );

        $this->assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className2));
        $this->assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
        );

        $this->assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className3));
        $this->assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className3,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
        );

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorName);
        self::assertIsObject($object->dependency1);
        self::assertIsObject($object->dependency2);
        self::assertIsObject($object->dependency3);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $object->dependency1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $object->dependency2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $object->dependency3);
    }

    public function testCompliesWithNullableVariable(): void
    {
        $className = 'TestClass'.self::getNextGeneratedClassNumber();
        $files = [self::GENERATED_CLASS_STUB_PATH."$className.php"];
        $config = $this->getConfig(services: ['include' => $files]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php",
            className: $className,
            hasConstructor: true,
            constructorArguments: ['public readonly ?string $arg'],
        );

        $container = new Container($config);
        $container->compile();

        $classFullNamespace = self::GENERATED_CLASS_NAMESPACE.$className;
        self::assertIsObject($container);

        $class = $container->get($classFullNamespace);
        self::assertInstanceOf($classFullNamespace, $class);
        self::assertNull($class->arg);
    }

    public function testDoesNotCompileDueToCircularExceptionByTaggedBinding(): void
    {
        $className = 'TestClass'.self::getNextGeneratedClassNumber();
        $files = [self::GENERATED_CLASS_STUB_PATH."$className.php"];
        $config = $this->getConfig(services: ['include' => $files]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php",
            className: $className,
            attributes: [sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'circular')],
            hasConstructor: true,
            constructorArguments: [
                sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'circular'),
                'public readonly iterable $arg',
            ],
        );

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(CircularReferenceException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate class "%s" as it has circular references "%s".',
                $className,
                $className,
            ),
        );

        $container = new Container($config);
        $container->compile();
    }

    /**
     * @dataProvider getDataForDoesNotCompileDueToInternalClassDependencyTest
     */
    public function testDoesNotCompileDueToInternalClassDependency(
        string $className,
        string $constructorArgument,
        string $argumentClassName,
    ): void {
        $files = [self::GENERATED_CLASS_STUB_PATH."$className.php"];
        $config = $this->getConfig(services: ['include' => $files]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php",
            className: $className,
            hasConstructor: true,
            constructorArguments: [$constructorArgument],
        );

        $this->expectException(UninstantiableEntryException::class);
        $this->expectExceptionMessage(sprintf('Cannot resolve internal entry "%s".', $argumentClassName));

        $container = new Container($config);
        $container->compile();
    }

    public function testDoesNotCompileDueToNonExistentBoundVariable(): void
    {
        $className = 'TestClass'.self::getNextGeneratedClassNumber();
        $files = [self::GENERATED_CLASS_STUB_PATH."$className.php"];
        $config = $this->getConfig(services: ['include' => $files]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php",
            className: $className,
            hasConstructor: true,
            constructorArguments: [
                'public readonly int $age,',
            ],
        );

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "%s::%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
                'age',
                'int',
            ),
        );

        $container = new Container($config);
        $container->compile();
    }

    /**
     * @dataProvider getDataForDoesNotCompileDueToNotDeterminedArgumentTypeTest
     */
    public function testDoesNotCompileDueToNotDeterminedArgumentType(
        string $className,
        string $constructorArgument,
        string $exceptionMessage,
    ): void {
        $classPath = self::GENERATED_CLASS_STUB_PATH.$className.'.php';
        $config = $this->getConfig(services: ['include' => [$classPath]]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php",
            className: $className,
            hasConstructor: true,
            constructorArguments: [$constructorArgument],
        );

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $container = new Container($config);
        $container->compile();
    }

    /**
     * @dataProvider getDataForDoesNotCompileDueToVariableBindingErrorsTest
     */
    public function testDoesNotCompileDueToVariableBindingErrors(
        string $className,
        array $constructorArguments,
        string $exception,
        string $exceptionMessage,
    ): void {
        $files = [self::GENERATED_CLASS_STUB_PATH."$className.php"];
        $config = $this->getConfig(
            services: ['include' => $files],
            classBindings: [
                self::GENERATED_CLASS_NAMESPACE.$className => ['bind' => ['$arg' => 'env(ENV_STRING_VAR)']],
            ],
        );
        $env = ['ENV_STRING_VAR' => 'string'];

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php",
            className: $className,
            hasConstructor: true,
            constructorArguments: $constructorArguments,
        );

        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        $container = new Container($config, $env);
        $container->compile();
    }

    public function testDoesNotCompileWithExcludedDependency(): void
    {
        $collectorName = 'TestClass'.self::getNextGeneratedClassNumber();
        $className1 = 'TestClass'.self::getNextGeneratedClassNumber();
        $className2 = 'TestClass'.self::getNextGeneratedClassNumber();
        $className3 = 'TestClass'.self::getNextGeneratedClassNumber();

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php",
            className: $className1,
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php",
            className: $className2,
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php",
            className: $className3,
        );
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorName.php",
            className: $collectorName,
            hasConstructor: true,
            constructorArguments: [
                sprintf('public readonly %s $dependency1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1),
                sprintf('public readonly %s $dependency2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                sprintf('public readonly %s $dependency3,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
            ],
        );

        $includeFiles = [
            self::GENERATED_CLASS_STUB_PATH.$collectorName.'.php',
            self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
        ];
        $excludeFiles = [
            self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
        ];

        $config = $this->getConfig(services: ['include' => $includeFiles, 'exclude' => $excludeFiles]);

        $this->expectException(NonAutowirableClassException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot autowire class "%s" as it is in "exclude" config parameter.',
                self::GENERATED_CLASS_NAMESPACE.$className3,
            ),
        );

        $container = new Container($config);
        $container->compile();
    }

    public function testDoesNotCompileWithNonAutowirableAttributeClass(): void
    {
        $collectorClassName = 'TestClass'.self::getNextGeneratedClassNumber();
        $invalidClassName = 'TestClass'.self::getNextGeneratedClassNumber();

        $classPath = self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php';
        $config = $this->getConfig(services: ['include' => [$classPath]]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php",
            className: $collectorClassName,
            hasConstructor: true,
            constructorArguments: [
                sprintf(
                    'public readonly %s $arg',
                    self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$invalidClassName,
                ),
            ],
        );

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$invalidClassName.php",
            className: $invalidClassName,
            attributes: [self::ATTRIBUTE_NON_AUTOWIRABLE_SIGNATURE],
            hasConstructor: true,
        );

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NonAutowirableClassException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Class "%s" has NonAutowirable attribute and cannot be autowired.',
                self::GENERATED_CLASS_NAMESPACE.$invalidClassName,
            ),
        );

        $container = new Container($config);
        $container->compile();
    }

    /**
     * @dataProvider getDataForDoesNotCompileWithUninstantiableEntryTest
     */
    public function testDoesNotCompileWithUninstantiableEntry(
        string $collectorClassName,
        string $invalidClassName,
        string $classNamePrefix,
        string $constructorVisibility,
        array $collectorConstructorArguments,
    ): void {
        $classPath = self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php';
        $config = $this->getConfig(services: ['include' => [$classPath]]);

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php",
            className: $collectorClassName,
            hasConstructor: true,
            constructorArguments: $collectorConstructorArguments,
        );

        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$invalidClassName.php",
            className: $invalidClassName,
            classNamePrefix: $classNamePrefix,
            constructorVisibility: $constructorVisibility,
            hasConstructor: true,
            constructorArguments: $collectorConstructorArguments,
        );

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(UninstantiableEntryException::class);
        $this->expectExceptionMessage(
            sprintf('Cannot instantiate entry with id "%s".', self::GENERATED_CLASS_NAMESPACE.$invalidClassName),
        );

        $container = new Container($config);
        $container->compile();
    }

    public function testWithoutCompiling(): void
    {
        $container = new Container(config: ['config_dir' => __DIR__]);

        $this->expectException(EntryNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Could not find entry "%s".', 'asd'));
        $container->get('asd');
    }

    private function getConfig(
        array $services = [],
        array $interfaceBindings = [],
        array $classBindings = [],
    ): array {
        $config = ['config_dir' => __DIR__];

        if ($services) {
            $config['services'] = $services;
        }

        if ($interfaceBindings) {
            $config['interface_bindings'] = $interfaceBindings;
        }

        if ($classBindings) {
            $config['class_bindings'] = $classBindings;
        }

        return $config;
    }
}

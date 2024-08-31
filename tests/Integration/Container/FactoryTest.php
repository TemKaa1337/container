<?php

declare(strict_types=1);

namespace Tests\Integration\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Exception\ClassFactoryException;
use Temkaa\SimpleContainer\Exception\Config\EntryNotFoundException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Model\Config\Factory;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;

/**
 * @SuppressWarnings(PHPMD)
 *
 * @psalm-suppress ArgumentTypeCoercion, MixedPropertyFetch, MixedAssignment, MixedArgumentTypeCoercion, UndefinedClass
 */
final class FactoryTest extends AbstractContainerTestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWhenClassIsFactoryForItself(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className1,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorVisibility('private')
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                    ])
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s $arg1, %s $arg2): self
                            {
                                return new self($arg1, $arg2);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();
        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class);
        self::assertSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWhenClassWithFactoryIsDecorator(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName().'interface';
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                            'dependency',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s $priorityTwoDecorator): %s
                            {
                                return new %s($priorityTwoDecorator);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            2,
                            'dependency',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                            'dependency',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className5.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $decorated = $container->get(self::GENERATED_CLASS_NAMESPACE.$interface);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $decorated->decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $decorated->decorated->decorated);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className5,
            $decorated->decorated->decorated->decorated,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWhenClassWithFactoryIsDecoratorWithMultipleFactoryArguments(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName().'interface';
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                            'decorated',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $var1,',
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        'public readonly int $var2,',
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s string $var1, %s $decorated, %s int $var2): %s
                            {
                                return new %s($var1, $decorated, $var2);
                            }
                            METHOD,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'string'),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, '1'),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            2,
                            'dependency',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                            'dependency',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className5.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $decorated = $container->get(self::GENERATED_CLASS_NAMESPACE.$interface);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $decorated->decorated);
        self::assertEquals('string', $decorated->decorated->var1);
        self::assertEquals(1, $decorated->decorated->var2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $decorated->decorated->decorated);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className5,
            $decorated->decorated->decorated->decorated,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWhenClassWithFactoryIsDecoratorWithNonSingletonDecorators(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName().'interface';
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                            'dependency',
                        ),
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s $priorityTwoDecorator): %s
                            {
                                return new %s($priorityTwoDecorator);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            2,
                            'dependency',
                        ),
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                            'dependency',
                        ),
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className5.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $decoratedInstance1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$interface);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $decoratedInstance1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $decoratedInstance1->decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $decoratedInstance1->decorated->decorated);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className5,
            $decoratedInstance1->decorated->decorated->decorated,
        );

        $decoratedInstance2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$interface);
        self::assertNotSame($decoratedInstance2, $decoratedInstance1);
        self::assertNotSame($decoratedInstance2->decorated, $decoratedInstance1->decorated);
        self::assertNotSame($decoratedInstance2->decorated->decorated, $decoratedInstance1->decorated->decorated);
        self::assertNotSame(
            $decoratedInstance2->decorated->decorated->decorated,
            $decoratedInstance1->decorated->decorated->decorated,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithAbstractClassAsAFactoryReturnType(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface])
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(): %s
                            {
                                return new %s;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            self::GENERATED_CLASS_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setPrefix('abstract class'),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithBoundVariablesForFactoryMethodFromConfig(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $enum = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        'public readonly string $arg3,',
                        'public readonly int $arg4,',
                        sprintf('public readonly %s $arg5,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$enum),
                        'public readonly int $arg6,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$enum.php")
                    ->setName($enum)
                    ->setPrefix('enum')
                    ->setMethods([
                        'case FirstCase;',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'private readonly int $varFour,',
                    ])
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public function create(%s $class1, %s $class2, string $varOne, int $varTwo, \UnitEnum $varThree): %s
                            {
                                return new %s($class1, $class2, $varOne, $varTwo, $varThree, $this->varFour);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    factory: new Factory(
                        id: self::GENERATED_CLASS_NAMESPACE.$className4,
                        method: 'create',
                        boundedVariables: [
                            'varOne'   => 'env(APP_BOUND_VAR)',
                            'varTwo'   => 'env(ENV_VAR_2)',
                            'varThree' => constant(self::GENERATED_CLASS_NAMESPACE.$enum.'::FirstCase'),
                        ],
                    ),
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className4,
                    variableBindings: [
                        '$varFour' => 'env(ENV_INT_VAL)',
                    ],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class1->arg2);
        self::assertEquals('bound_variable_value', $class1->arg3);
        self::assertEquals(10, $class1->arg4);
        self::assertEquals(constant(self::GENERATED_CLASS_NAMESPACE.$enum.'::FirstCase'), $class1->arg5);
        self::assertEquals(3, $class1->arg6);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithBoundedTaggedIteratorToFactoryMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly array $arg1,',
                        'public readonly string $arg2,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s array $args, %s string $envVar): %s
                            {
                                return new %s($args, $envVar);
                            }
                            METHOD,
                            sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'tag'),
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'some_string'),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'tag'),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'tag'),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertSame(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className4),
            ],
            $class->arg1,
        );
        self::assertEquals('some_string', $class->arg2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithClassFactoryWhichIsDecoratorWithDynamicMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface])
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                            'property',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public function create(%s $arg): %s
                            {
                                return new %s($arg);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                            'property',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class1->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class1->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class1->arg->decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class1->arg->decorated->decorated);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithClassFactoryWhichIsDecoratorWithStaticMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface])
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                            'property',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s $arg): %s
                            {
                                return new %s($arg);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                            'property',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class1->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class1->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class1->arg->decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class1->arg->decorated->decorated);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDynamicFactoryFromAttribute(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('private readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        sprintf('private readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                    ])
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public function create(): %s
                            {
                                return new %s($this);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf($class2::class, $class1->arg1);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDynamicFactoryFromConfig(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('private readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        sprintf('private readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                    ])
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public function create(): %s
                            {
                                return new %s($this);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    factory: new Factory(
                        id: self::GENERATED_CLASS_NAMESPACE.$className2,
                        method: 'create',
                        boundedVariables: [],
                    ),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf($class2::class, $class1->arg1);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDynamicFactoryWithFactoryMethodParameters(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        $className6 = ClassGenerator::getClassName();
        $factory1 = ClassGenerator::getClassName();
        $factory2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factory1,
                            'create',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$factory1.php")
                    ->setName($factory1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('private readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ])
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public function create(%s $class2): %s
                            {
                                return new %s($this->arg1, $class2);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className5),
                        sprintf('public readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className6),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className6.php")
                    ->setName($className6),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$factory2.php")
                    ->setName($factory2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('private readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className5),
                    ])
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public function create(%s $class2): %s
                            {
                                return new %s($this->arg1, $class2);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className6,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className5.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className6.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className4,
                    factory: new Factory(
                        id: self::GENERATED_CLASS_NAMESPACE.$factory2,
                        method: 'create',
                        boundedVariables: [],
                    ),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class1->arg2);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className5, $class2->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className6, $class2->arg2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInjectedClassWithFactoryIntoMultipleClasses(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
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
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(): %s
                            {
                                return new %s();
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();
        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        $class4 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);

        self::assertSame($class1, $class3->arg1);
        self::assertSame($class1, $class4->arg1);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInjectedNonSingletonClassWithFactoryIntoMultipleClasses(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
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
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(): %s
                            {
                                return new %s();
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();
        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        $class4 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);

        self::assertNotSame($class1, $class3->arg1);
        self::assertNotSame($class1, $class4->arg1);
        self::assertNotSame($class3->arg1, $class4->arg1);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInterfaceAsAFactoryReturnType(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(): %s
                            {
                                return new %s;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithNonSingletonClassesInjectedIntoFactoryMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        sprintf('public readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s $arg1, %s $arg2): %s
                            {
                                return new %s($arg1, $arg2);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $classInstance1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $classInstance2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertNotSame($classInstance1, $classInstance2);
        self::assertNotSame($classInstance1->arg1, $classInstance2->arg1);
        self::assertNotSame($classInstance1->arg2, $classInstance2->arg2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithStaticFactoryFromAttribute(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('private readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        sprintf('private readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                    ])
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(): %s
                            {
                                return new %s(new self(new %s(), new %s()));
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf($class2::class, $class1->arg1);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithStaticFactoryFromConfig(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('private readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        sprintf('private readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                    ])
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(): %s
                            {
                                return new %s(new self(new %s(), new %s()));
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    factory: new Factory(
                        id: self::GENERATED_CLASS_NAMESPACE.$className2,
                        method: 'create',
                        boundedVariables: [],
                    ),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf($class2::class, $class1->arg1);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithStaticFactoryWithFactoryMethodParameters(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        $className6 = ClassGenerator::getClassName();
        $factory1 = ClassGenerator::getClassName();
        $factory2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factory1,
                            'create',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$factory1.php")
                    ->setName($factory1)
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s $class1, %s $class2): %s
                            {
                                return new %s($class1, $class2);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className5),
                        sprintf('public readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className6),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className6.php")
                    ->setName($className6),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$factory2.php")
                    ->setName($factory2)
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s $class1, %s $class2): %s
                            {
                                return new %s($class1, $class2);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className5,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className6,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className5.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className6.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className4,
                    factory: new Factory(
                        id: self::GENERATED_CLASS_NAMESPACE.$factory2,
                        method: 'create',
                        boundedVariables: [],
                    ),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class1->arg2);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className5, $class2->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className6, $class2->arg2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToFactoryDoesNotHaveReturnType(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create()
                            {
                                return new %s($this);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Factory method "%s::create" for class "%s" must have a return type.',
                self::GENERATED_CLASS_NAMESPACE.$className2,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToFactoryReturnsOtherObject(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(): %s
                            {
                                return new %s($this);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Factory method "%s::create" for class "%s" must return compatible instance, got "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className2,
                self::GENERATED_CLASS_NAMESPACE.$className1,
                self::GENERATED_CLASS_NAMESPACE.$className2,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToNonExistingFactoryClassFromAttribute(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            'SomeNonExistingClass',
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(EntryNotFoundException::class);
        $this->expectExceptionMessage('Class "SomeNonExistingClass" not found.');

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToNonExistingFactoryClassFromConfig(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    factory: new Factory(id: 'SomeNonExistingClass', method: 'create', boundedVariables: []),
                ),
            ],
        );

        $this->expectException(EntryNotFoundException::class);
        $this->expectExceptionMessage('Class "SomeNonExistingClass" not found.');

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToNonExistingFactoryMethodFromAttribute(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not find method named "%s" in class "%s" for factory of class "%s".',
                'create',
                self::GENERATED_CLASS_NAMESPACE.$className3,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToNonExistingFactoryMethodFromConfig(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    factory: new Factory(
                        id: self::GENERATED_CLASS_NAMESPACE.$className3,
                        method: 'create',
                        boundedVariables: [],
                    ),
                ),
            ],
        );

        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not find method named "%s" in class "%s" for factory of class "%s".',
                'create',
                self::GENERATED_CLASS_NAMESPACE.$className3,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWhenClassWithFactoryIsDecoratorWithMultipleFactoryArgumentsAndNoDecoratorNamedVariable(
    ): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName().'interface';
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                            'decorated',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $var1,',
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        'public readonly int $var2,',
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s string $var1, %s $priorityTwoDecorator, %s int $var2): %s
                            {
                                return new %s($var1, $priorityTwoDecorator, $var2);
                            }
                            METHOD,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'string'),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, '1'),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            2,
                            'dependency',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                            'dependency',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className5.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(UnresolvableArgumentException::class);
        // TODO: make exception message more concrete
        $this->expectExceptionMessage(
            sprintf(
                'Could not resolve decorated class in class "%s" as it does not have argument named "decorated".',
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWhenFactoryMethodIsNotAccessible(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
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
                    ->setPrefix('abstract class')
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public function create(): %s
                            {
                                return new %s();
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Factory method "%s::create" for class "%s" must be instantiable.',
                self::GENERATED_CLASS_NAMESPACE.$className2,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );
        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithPrivateConstructorAndSeparateFactoryClass(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorVisibility('private')
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
                    ->setMethods([
                        sprintf(
                            <<<'METHOD'
                            public function create(): %s
                            {
                                return new %s();
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Invalid factory method "%s::create" for class "%s", as class "%s" has inaccessible constructor.',
                self::GENERATED_CLASS_NAMESPACE.$className2,
                self::GENERATED_CLASS_NAMESPACE.$className1,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );
        (new ContainerBuilder())->add($config)->build();
    }
}

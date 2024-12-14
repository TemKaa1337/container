<?php

declare(strict_types=1);

namespace Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Container\Attribute\Bind\Instance;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Exception\NonAutowirableClassException;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;
use function realpath;
use function sprintf;

/**
 * @psalm-suppress all
 * @SuppressWarnings(PHPMD)
 */
final class MultipleConfigTest extends AbstractContainerTestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDifferentClassBindings(): void
    {
        $this->markTestSkipped('Think about correctness of this test later.');

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
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly mixed $mixed,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $config1 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            ],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className4,
                    variableBindings: [
                        '$mixed' => 'mixed_string',
                    ],
                ),
            ],
        );
        $config2 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            ],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className4,
                    variableBindings: [
                        '$mixed' => 10.1,
                    ],
                ),
            ],
        );
        $config3 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            ],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className4,
                    variableBindings: [
                        '$mixed' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className1),
                    ],
                ),
            ],
        );

        $container = (new ContainerBuilder())
            ->add($config1)
            ->add($config2)
            ->add($config3)
            ->build();

        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className1));
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className2));
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className3));
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className4));

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class4 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);
        self::assertSame($class1, $class4->mixed);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDifferentDecorators(): void
    {
        $class = ClassGenerator::getClassName();
        $decorator1 = ClassGenerator::getClassName();
        $decorator2 = ClassGenerator::getClassName();
        $decorator3 = ClassGenerator::getClassName();
        $decorator4 = ClassGenerator::getClassName();
        $collector = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$class.php")
                    ->setName($class)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$decorator1.php")
                    ->setName($decorator1)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ])
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $class,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$decorator2.php")
                    ->setName($decorator2)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ])
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            3,
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $class,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$decorator3.php")
                    ->setName($decorator3)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ])
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            -1,
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $class,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$decorator4.php")
                    ->setName($decorator4)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ])
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            2,
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $class,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collector.php")
                    ->setName($collector)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $class,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $config1 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$class.php",
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$decorator1.php",
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$interface.php",
            ],
        );
        $config2 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$decorator2.php",
            ],
        );
        $config3 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$decorator3.php",
            ],
        );
        $config4 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$decorator4.php",
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$collector.php",
            ],
        );

        $container = (new ContainerBuilder())
            ->add($config1)
            ->add($config2)
            ->add($config3)
            ->add($config4)
            ->build();

        $collectorClass = $container->get(self::GENERATED_CLASS_NAMESPACE.$collector);
        $decoratedClass = $container->get(self::GENERATED_CLASS_NAMESPACE.$interface);

        $classClass = $container->get(self::GENERATED_CLASS_NAMESPACE.$class);
        $decorator1Class = $container->get(self::GENERATED_CLASS_NAMESPACE.$decorator1);
        $decorator2Class = $container->get(self::GENERATED_CLASS_NAMESPACE.$decorator2);
        $decorator3Class = $container->get(self::GENERATED_CLASS_NAMESPACE.$decorator3);
        $decorator4Class = $container->get(self::GENERATED_CLASS_NAMESPACE.$decorator4);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $collectorClass->class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $decoratedClass);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$decorator3, $collectorClass->class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$decorator3, $decoratedClass);

        self::assertSame($decorator3Class, $collectorClass->class);
        self::assertSame($decoratedClass, $collectorClass->class);

        self::assertSame($decorator1Class, $collectorClass->class->class);
        self::assertSame($decoratedClass->class, $collectorClass->class->class);

        self::assertSame($decorator4Class, $collectorClass->class->class->class);
        self::assertSame($decoratedClass->class->class, $collectorClass->class->class->class);

        self::assertSame($decorator2Class, $collectorClass->class->class->class->class);
        self::assertSame($decoratedClass->class->class->class, $collectorClass->class->class->class->class);

        self::assertSame($classClass, $collectorClass->class->class->class->class->class);
        self::assertSame(
            $decoratedClass->class->class->class->class,
            $collectorClass->class->class->class->class->class,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDifferentInterfaceBindings(): void
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
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $class,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $config1 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            ],
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interface => self::GENERATED_CLASS_NAMESPACE.$className1,
            ],
        );
        $config2 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            ],
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interface => self::GENERATED_CLASS_NAMESPACE.$className2,
            ],
        );
        $config3 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            ],
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interface => self::GENERATED_CLASS_NAMESPACE.$className3,
            ],
        );

        $container = (new ContainerBuilder())
            ->add($config1)
            ->add($config2)
            ->add($config3)
            ->build();

        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className1));
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className2));
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className3));
        self::assertSame($class3, $container->get(self::GENERATED_CLASS_NAMESPACE.$interface));

        $class4 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);
        self::assertSame($class3, $class4->class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithExcludedClassInOneOfConfigs(): void
    {
        $this->markTestSkipped('Think about correctness of this test later.');

        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1),
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

        $config1 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            ],
            excludedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            ],
        );
        $config2 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            ],
            excludedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            ],
        );

        $container = (new ContainerBuilder())->add($config1)->add($config2)->build();

        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className1));
        self::assertFalse($container->has(self::GENERATED_CLASS_NAMESPACE.$className2));
        self::assertFalse($container->has(self::GENERATED_CLASS_NAMESPACE.$className3));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithMultipleConfigs(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1),
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

        $config1 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            ],
        );
        $config2 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            ],
        );

        $container = (new ContainerBuilder())->add($config1)->add($config2)->build();

        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className1));
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className2));
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className3));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithExcludedClassInOneOfConfigs(): void
    {
        $this->markTestSkipped('Think about correctness of this test later.');

        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $class,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->generate();

        $config1 = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            ],
        );
        $config2 = $this->generateConfig(
            excludedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            ],
        );

        $this->expectException(NonAutowirableClassException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot autowire class "%s" as it is in "exclude" config parameter.',
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config1)->add($config2)->build();
    }
}

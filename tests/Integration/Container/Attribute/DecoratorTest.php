<?php

declare(strict_types=1);

namespace Container\Attribute;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Exception\UnresolvableArgumentException;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

/**
 * @psalm-suppress all
 * @SuppressWarnings(PHPMD)
 */
final class DecoratorTest extends AbstractContainerTestCase
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
    public function testCompilesWithDecorator(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1.'::class',
                            0,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $decorated = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $decorator = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $decorator);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $decorated->dependency);
        self::assertSame($decorated, $decorator);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDecoratorByInterfaceWhereDecoratorContainsMultipleConstructorArguments(): void
    {
        $interfaceName1 = ClassGenerator::getClassName();
        $interfaceName2 = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $collectorClassName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName1.php")
                    ->setName($interfaceName1)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1.'::class',
                            0,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE,
                            'env(ENV_VAR_1)',
                        ),
                        'public readonly string $arg,',
                        sprintf(
                            'public readonly %s $inner,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                        ),
                        sprintf(self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE, 'Interface2'),
                        'public readonly iterable $dependency,',
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName2.php")
                    ->setName($interfaceName2)
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'Interface2')])
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2])
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2])
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$collectorClassName.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName1 => self::GENERATED_CLASS_NAMESPACE.$className1,
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $decorated = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName1);
        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);

        self::assertSame($class2, $decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorClassName, $collector);

        self::assertSame($class2, $collector->dependency1);

        self::assertEquals('test_one', $class2->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class2->inner);

        self::assertCount(2, $class2->dependency);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class2->dependency[0]);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class2->dependency[1]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDecoratorWithoutDecoratedServiceInjected(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1.'::class',
                            0,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $decorated = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $decorator = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $decorator);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithMultipleDecoratorsByClass(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1.'::class',
                            3,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1.'::class',
                            2,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1.'::class',
                            1,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $decorated = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className3,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className4,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className4),
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $decorated->dependency);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $decorated->dependency->dependency);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $decorated->dependency->dependency->dependency,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithMultipleDecoratorsByDifferentInterfaces(): void
    {
        $interfaceName1 = ClassGenerator::getClassName();
        $interfaceName2 = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        $className6 = ClassGenerator::getClassName();
        $className7 = ClassGenerator::getClassName();
        $className8 = ClassGenerator::getClassName();
        $collectorClassName1 = ClassGenerator::getClassName();
        $collectorClassName2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName1.php")
                    ->setName($collectorClassName1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                        ),
                        sprintf(
                            'public readonly %s $dependency2,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                        sprintf(
                            'public readonly %s $dependency3,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                        sprintf(
                            'public readonly %s $dependency4,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        sprintf(
                            'public readonly %s $dependency5,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName2.php")
                    ->setName($collectorClassName2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2,
                        ),
                        sprintf(
                            'public readonly %s $dependency2,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className8,
                        ),
                        sprintf(
                            'public readonly %s $dependency3,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className7,
                        ),
                        sprintf(
                            'public readonly %s $dependency4,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className6,
                        ),
                        sprintf(
                            'public readonly %s $dependency5,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className5,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName1.php")
                    ->setName($interfaceName1)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName2.php")
                    ->setName($interfaceName2)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1.'::class',
                            3,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dep',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1.'::class',
                            2,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $class',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1.'::class',
                            1,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $property',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className6.php")
                    ->setName($className6)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2.'::class',
                            3,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dep',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className7.php")
                    ->setName($className7)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2.'::class',
                            2,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $class',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className8.php")
                    ->setName($className8)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2.'::class',
                            1,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $property',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$collectorClassName1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className5.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className6.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$collectorClassName2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className7.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className8.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $decorated = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName1);
        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorClassName1, $collector);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $collector->dependency1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $collector->dependency1->property);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $collector->dependency1->property->class,
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $collector->dependency1->property->class->dep,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $collector->dependency2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $collector->dependency2->property);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $collector->dependency2->property->class,
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $collector->dependency2->property->class->dep,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $collector->dependency3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collector->dependency3->class);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $collector->dependency3->class->dep,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collector->dependency4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collector->dependency4->dep);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collector->dependency5);

        $class4 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class2->dep);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class3->class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class4->property);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $decorated->property);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $decorated->property->class);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $decorated->property->class->dep,
        );

        self::assertSame($decorated, $collector->dependency1);
        self::assertSame($decorated, $collector->dependency2);
        self::assertSame($class4, $collector->dependency2);
        self::assertSame($class3, $collector->dependency3);
        self::assertSame($class2, $collector->dependency4);
        self::assertSame($class1, $collector->dependency5);
        self::assertSame($decorated->property, $collector->dependency3);
        self::assertSame($decorated->property->class, $collector->dependency4);
        self::assertSame($decorated->property->class->dep, $collector->dependency5);

        $decorated = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName2);
        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName2);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorClassName2, $collector);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className8, $collector->dependency1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className7, $collector->dependency1->property);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className6,
            $collector->dependency1->property->class,
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className5,
            $collector->dependency1->property->class->dep,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className8, $collector->dependency2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className7, $collector->dependency2->property);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className6,
            $collector->dependency2->property->class,
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className5,
            $collector->dependency2->property->class->dep,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className7, $collector->dependency3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className6, $collector->dependency3->class);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className5,
            $collector->dependency3->class->dep,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className6, $collector->dependency4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className5, $collector->dependency4->dep);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className5, $collector->dependency5);

        $class4 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className8);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className7);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className6);
        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className5);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className8, $class4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className7, $class3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className6, $class2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className5, $class1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className5, $class2->dep);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className6, $class3->class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className7, $class4->property);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className8, $decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className7, $decorated->property);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className6, $decorated->property->class);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className5,
            $decorated->property->class->dep,
        );

        self::assertSame($decorated, $collector->dependency1);
        self::assertSame($decorated, $collector->dependency2);
        self::assertSame($class4, $collector->dependency2);
        self::assertSame($class3, $collector->dependency3);
        self::assertSame($class2, $collector->dependency4);
        self::assertSame($class1, $collector->dependency5);
        self::assertSame($decorated->property, $collector->dependency3);
        self::assertSame($decorated->property->class, $collector->dependency4);
        self::assertSame($decorated->property->class->dep, $collector->dependency5);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithMultipleDecoratorsByInterface(): void
    {
        $interfaceName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $collectorClassName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                        sprintf(
                            'public readonly %s $dependency2,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                        sprintf(
                            'public readonly %s $dependency3,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                        sprintf(
                            'public readonly %s $dependency4,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        sprintf(
                            'public readonly %s $dependency5,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            3,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dep',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            2,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $class',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            1,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $property',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$collectorClassName.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName => self::GENERATED_CLASS_NAMESPACE.$className1,
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $decorated = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName);
        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorClassName, $collector);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $collector->dependency1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $collector->dependency1->property);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $collector->dependency1->property->class,
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $collector->dependency1->property->class->dep,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $collector->dependency2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $collector->dependency2->property);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $collector->dependency2->property->class,
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $collector->dependency2->property->class->dep,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $collector->dependency3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collector->dependency3->class);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $collector->dependency3->class->dep,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collector->dependency4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collector->dependency4->dep);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collector->dependency5);

        $class4 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class2->dep);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class3->class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class4->property);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $decorated->property);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $decorated->property->class);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $decorated->property->class->dep,
        );

        self::assertSame($decorated, $collector->dependency1);
        self::assertSame($decorated, $collector->dependency2);
        self::assertSame($class4, $collector->dependency2);
        self::assertSame($class3, $collector->dependency3);
        self::assertSame($class2, $collector->dependency4);
        self::assertSame($class1, $collector->dependency5);
        self::assertSame($decorated->property, $collector->dependency3);
        self::assertSame($decorated->property->class, $collector->dependency4);
        self::assertSame($decorated->property->class->dep, $collector->dependency5);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithMultipleDecoratorsByInterfaceDeclaredAsNonSingletons(): void
    {
        $interfaceName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $collectorClassName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                        sprintf(
                            'public readonly %s $dependency2,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                        sprintf(
                            'public readonly %s $dependency3,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                        sprintf(
                            'public readonly %s $dependency4,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        sprintf(
                            'public readonly %s $dependency5,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ])
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface')
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName])
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            3,
                        ),
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            2,
                        ),
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            1,
                        ),
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$collectorClassName.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName => self::GENERATED_CLASS_NAMESPACE.$className1,
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $decorated = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName);
        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorClassName, $collector);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $collector->dependency1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $collector->dependency1->dependency);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $collector->dependency1->dependency->dependency,
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $collector->dependency1->dependency->dependency->dependency,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $collector->dependency2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $collector->dependency2->dependency);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $collector->dependency2->dependency->dependency,
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $collector->dependency2->dependency->dependency->dependency,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $collector->dependency3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collector->dependency3->dependency);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $collector->dependency3->dependency->dependency,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collector->dependency4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collector->dependency4->dependency);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collector->dependency5);

        $class4 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class2->dependency);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class3->dependency);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class4->dependency);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $decorated->dependency);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $decorated->dependency->dependency);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $decorated->dependency->dependency->dependency,
        );

        self::assertNotSame($decorated, $collector->dependency1);
        self::assertNotSame($decorated, $collector->dependency2);
        self::assertNotSame($class4, $collector->dependency2);
        self::assertNotSame($class3, $collector->dependency3);
        self::assertNotSame($class2, $collector->dependency4);
        self::assertNotSame($class1, $collector->dependency5);
        self::assertNotSame($decorated->dependency, $collector->dependency3);
        self::assertNotSame($decorated->dependency->dependency, $collector->dependency4);
        self::assertNotSame($decorated->dependency->dependency->dependency, $collector->dependency5);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithMultipleDecoratorsByInterfaceWhichIsNotBoundedToClass(): void
    {
        $interfaceName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $collectorClassName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                        sprintf(
                            'public readonly %s $dependency2,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                        sprintf(
                            'public readonly %s $dependency3,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                        sprintf(
                            'public readonly %s $dependency4,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        sprintf(
                            'public readonly %s $dependency5,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            3,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dep',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            2,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $class',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            1,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $property',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$collectorClassName.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $decorated = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName);
        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorClassName, $collector);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $collector->dependency1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $collector->dependency1->property);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $collector->dependency1->property->class,
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $collector->dependency1->property->class->dep,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $collector->dependency2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $collector->dependency2->property);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $collector->dependency2->property->class,
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $collector->dependency2->property->class->dep,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $collector->dependency3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collector->dependency3->class);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $collector->dependency3->class->dep,
        );

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collector->dependency4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collector->dependency4->dep);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collector->dependency5);

        $class4 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class2->dep);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class3->class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class4->property);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $decorated->property);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $decorated->property->class);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $decorated->property->class->dep,
        );

        self::assertSame($decorated, $collector->dependency1);
        self::assertSame($decorated, $collector->dependency2);
        self::assertSame($class4, $collector->dependency2);
        self::assertSame($class3, $collector->dependency3);
        self::assertSame($class2, $collector->dependency4);
        self::assertSame($class1, $collector->dependency5);
        self::assertSame($decorated->property, $collector->dependency3);
        self::assertSame($decorated->property->class, $collector->dependency4);
        self::assertSame($decorated->property->class->dep, $collector->dependency5);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithTwoDecoratorsWithSamePriority(): void
    {
        $interfaceName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            0,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dep',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            0,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dep',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $decorated = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $decorated);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $decorated->dep);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $decorated->dep->dep);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithDecoratorTypeHintedAsObject(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1.'::class',
                            0,
                        ),
                    ])
                    ->setConstructorArguments(['public readonly object $dependency']),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "dependency::object".',
                self::GENERATED_CLASS_NAMESPACE.$className2,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }
}

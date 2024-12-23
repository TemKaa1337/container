<?php

declare(strict_types=1);

namespace Tests\Integration\Container\Config;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;
use function sprintf;

/**
 * @psalm-suppress all
 * @SuppressWarnings(PHPMD)
 */
final class InstanceOfIteratorTest extends AbstractContainerTestCase
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
    public function testCompilesWithConfigPrecedence(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $interfaceName1 = ClassGenerator::getClassName();
        $interfaceName2 = ClassGenerator::getClassName();
        $interfaceName3 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName2.php")
                    ->setName($interfaceName2)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName1.php")
                    ->setName($interfaceName1)
                    ->setPrefix('interface')
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName3.php")
                    ->setName($interfaceName3)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_INSTANCE_OF_ITERATOR_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName3.'::class',
                        ),
                        'public readonly iterable $dependency,',
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: [
                        '$dependency' => new InstanceOfIterator(self::GENERATED_CLASS_NAMESPACE.$interfaceName2),
                    ],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertEquals(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
            ],
            $collector->dependency,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDifferentFormats(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly array $list,',
                        'public readonly array $arrayWithNamespaceKey,',
                        'public readonly array $arrayWithClassNameKey,',
                        'public readonly array $arrayWithCustomKey,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    variableBindings: [
                        '$list'                  => new InstanceOfIterator(
                            self::GENERATED_CLASS_NAMESPACE.$interfaceName,
                        ),
                        '$arrayWithNamespaceKey' => new InstanceOfIterator(
                            self::GENERATED_CLASS_NAMESPACE.$interfaceName,
                            format: IteratorFormat::ArrayWithClassNamespaceKey,
                        ),
                        '$arrayWithClassNameKey' => new InstanceOfIterator(
                            self::GENERATED_CLASS_NAMESPACE.$interfaceName,
                            format: IteratorFormat::ArrayWithClassNameKey,
                        ),
                        '$arrayWithCustomKey'    => new InstanceOfIterator(
                            self::GENERATED_CLASS_NAMESPACE.$interfaceName,
                            format: IteratorFormat::ArrayWithCustomKey,
                            customFormatMapping: [
                                self::GENERATED_CLASS_NAMESPACE.$className2 => 'class2',
                                self::GENERATED_CLASS_NAMESPACE.$className3 => 'class3',
                            ],
                        ),
                    ],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $composite = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);

        self::assertSame(
            [$class2, $class3],
            $composite->list,
        );
        self::assertSame(
            [
                self::GENERATED_CLASS_NAMESPACE.$className2 => $class2,
                self::GENERATED_CLASS_NAMESPACE.$className3 => $class3,
            ],
            $composite->arrayWithNamespaceKey,
        );
        self::assertSame(
            [
                $className2 => $class2,
                $className3 => $class3,
            ],
            $composite->arrayWithClassNameKey,
        );
        self::assertSame(
            [
                'class2' => $class2,
                'class3' => $class3,
            ],
            $composite->arrayWithCustomKey,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInstanceOfAbstractClassIteratorAsNonSingleton(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $abstractClass1 = ClassGenerator::getClassName();
        $abstractClass2 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$abstractClass1.php")
                    ->setName($abstractClass1)
                    ->setPrefix('abstract class')
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass2]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$abstractClass2.php")
                    ->setName($abstractClass2)
                    ->setPrefix('abstract class'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly iterable $dependency,',
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    singleton: false,
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className2,
                    singleton: false,
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: [
                        '$dependency' => new InstanceOfIterator(self::GENERATED_CLASS_NAMESPACE.$abstractClass2),
                    ],
                    singleton: false,
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $collectorInstance1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);
        $collectorInstance2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertNotSame($collectorInstance1, $collectorInstance2);
        self::assertNotSame($collectorInstance1->dependency[0], $collectorInstance2->dependency[0]);
        self::assertNotSame($collectorInstance1->dependency[1], $collectorInstance2->dependency[1]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collectorInstance1->dependency[0]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collectorInstance1->dependency[1]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collectorInstance2->dependency[0]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collectorInstance2->dependency[1]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInstanceOfAbstractClassIteratorAsSingleton(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $abstractClass1 = ClassGenerator::getClassName();
        $abstractClass2 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$abstractClass1.php")
                    ->setName($abstractClass1)
                    ->setPrefix('abstract class')
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass2]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$abstractClass2.php")
                    ->setName($abstractClass2)
                    ->setPrefix('abstract class'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly iterable $dependency,',
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: [
                        '$dependency' => new InstanceOfIterator(self::GENERATED_CLASS_NAMESPACE.$abstractClass2),
                    ],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertEquals(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
            ],
            $collector->dependency,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInstanceOfDefaultClassIteratorAsNonSingleton(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $parentClass1 = ClassGenerator::getClassName();
        $parentClass2 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$parentClass1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$parentClass1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$parentClass1.php")
                    ->setName($parentClass1)
                    ->setPrefix('class')
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$parentClass2]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$parentClass2.php")
                    ->setPrefix('class')
                    ->setName($parentClass2),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly iterable $dependency,',
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    singleton: false,
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className2,
                    singleton: false,
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: [
                        '$dependency' => new InstanceOfIterator(self::GENERATED_CLASS_NAMESPACE.$parentClass2),
                    ],
                    singleton: false,
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $collectorInstance1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);
        $collectorInstance2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertNotSame($collectorInstance1, $collectorInstance2);
        self::assertNotSame($collectorInstance1->dependency[0], $collectorInstance2->dependency[0]);
        self::assertNotSame($collectorInstance1->dependency[1], $collectorInstance2->dependency[1]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collectorInstance1->dependency[0]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collectorInstance1->dependency[1]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collectorInstance2->dependency[0]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collectorInstance2->dependency[1]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInstanceOfDefaultClassIteratorAsSingleton(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $parentClass1 = ClassGenerator::getClassName();
        $parentClass2 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$parentClass1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$parentClass1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$parentClass1.php")
                    ->setName($parentClass1)
                    ->setPrefix('class')
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$parentClass2]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$parentClass2.php")
                    ->setPrefix('class')
                    ->setName($parentClass2),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly iterable $dependency,',
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: [
                        '$dependency' => new InstanceOfIterator(self::GENERATED_CLASS_NAMESPACE.$parentClass2),
                    ],
                    singleton: false,
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertEquals(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
            ],
            $collector->dependency,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInstanceOfInterfaceIteratorAsNonSingleton(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $interfaceName1 = ClassGenerator::getClassName();
        $interfaceName2 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName2.php")
                    ->setName($interfaceName2)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName1.php")
                    ->setName($interfaceName1)
                    ->setPrefix('interface')
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly iterable $dependency,',
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    singleton: false,
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className2,
                    singleton: false,
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: [
                        '$dependency' => new InstanceOfIterator(self::GENERATED_CLASS_NAMESPACE.$interfaceName2),
                    ],
                    singleton: false,
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $collectorInstance1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);
        $collectorInstance2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertNotSame($collectorInstance1, $collectorInstance2);
        self::assertNotSame($collectorInstance1->dependency[0], $collectorInstance2->dependency[0]);
        self::assertNotSame($collectorInstance1->dependency[1], $collectorInstance2->dependency[1]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collectorInstance1->dependency[0]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collectorInstance1->dependency[1]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collectorInstance2->dependency[0]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collectorInstance2->dependency[1]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInstanceOfInterfaceIteratorAsSingleton(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $interfaceName1 = ClassGenerator::getClassName();
        $interfaceName2 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName2.php")
                    ->setName($interfaceName2)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName1.php")
                    ->setName($interfaceName1)
                    ->setPrefix('interface')
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly iterable $dependency,',
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: [
                        '$dependency' => new InstanceOfIterator(self::GENERATED_CLASS_NAMESPACE.$interfaceName2),
                    ],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertEquals(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
            ],
            $collector->dependency,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInstanceOfInterfaceWithExcludedClass(): void
    {
        $interfaceName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $composite = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $classWithInjectedComposite = ClassGenerator::getClassName();

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
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$composite.php")
                    ->setName($composite)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly array $handlers,',
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly array $handlers,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(
                        realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classWithInjectedComposite.php",
                    )
                    ->setName($classWithInjectedComposite)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $composite,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$composite.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$classWithInjectedComposite.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName => self::GENERATED_CLASS_NAMESPACE.$composite,
            ],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$composite,
                    variableBindings: [
                        '$handlers' => new InstanceOfIterator(
                            self::GENERATED_CLASS_NAMESPACE.$interfaceName,
                            exclude: [self::GENERATED_CLASS_NAMESPACE.$composite],
                        ),
                    ],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className3,
                    variableBindings: [
                        '$handlers' => new InstanceOfIterator(
                            self::GENERATED_CLASS_NAMESPACE.$interfaceName,
                            exclude: [self::GENERATED_CLASS_NAMESPACE.$composite],
                        ),
                    ],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $compositeObject = $container->get(self::GENERATED_CLASS_NAMESPACE.$composite);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$composite, $compositeObject);
        self::assertSame(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
            ],
            $compositeObject->handlers,
        );

        $compositeObject = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$composite, $compositeObject);
        self::assertSame(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
            ],
            $compositeObject->handlers,
        );

        $classWithHandlers = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $classWithHandlers);
        self::assertSame(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
            ],
            $classWithHandlers->handlers,
        );

        $classWithComposite = $container->get(self::GENERATED_CLASS_NAMESPACE.$classWithInjectedComposite);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classWithInjectedComposite, $classWithComposite);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$composite, $classWithComposite->composite);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interfaceName, $classWithComposite->composite);
        self::assertSame(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
            ],
            $classWithComposite->composite->handlers,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToMissingClassInCustomMappingFormat(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly array $dependency,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    variableBindings: [
                        '$dependency' => new InstanceOfIterator(
                            self::GENERATED_CLASS_NAMESPACE.$interfaceName,
                            format: IteratorFormat::ArrayWithCustomKey,
                            customFormatMapping: [
                                self::GENERATED_CLASS_NAMESPACE.$className3 => 'test',
                            ],
                        ),
                    ],
                ),
            ],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Missing class "%s" in "customFormatMapping".', self::GENERATED_CLASS_NAMESPACE.$className2),
        );

        (new ContainerBuilder())->add($config)->build();
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration\Container\Config;

use Temkaa\SimpleContainer\Attribute\Bind\InstanceOfIterator;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

final class InstanceOfIteratorTest extends AbstractContainerTestCase
{
    /**
     * @psalm-suppress InvalidClassConstantType
     */
    protected const string GENERATED_CLASS_STUB_PATH = '/../../../Fixture/Stub/Class/';

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
}

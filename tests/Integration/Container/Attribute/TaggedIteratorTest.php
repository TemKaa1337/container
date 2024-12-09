<?php

declare(strict_types=1);

namespace Container\Attribute;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
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
final class TaggedIteratorTest extends AbstractContainerTestCase
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
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')])
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE,
                            'tag',
                        ),
                        'public readonly array $list,',
                        sprintf(
                            self::ATTRIBUTE_TAGGED_ITERATOR_WITH_FULL_SIGNATURE,
                            'tag',
                            '',
                            '\\'.IteratorFormat::class.'::ArrayWithClassNamespaceKey',
                            '[]',
                        ),
                        'public readonly array $arrayWithNamespaceKey,',
                        sprintf(
                            self::ATTRIBUTE_TAGGED_ITERATOR_WITH_FULL_SIGNATURE,
                            'tag',
                            '',
                            '\\'.IteratorFormat::class.'::ArrayWithClassNameKey',
                            '[]',
                        ),
                        'public readonly array $arrayWithClassNameKey,',
                        sprintf(
                            self::ATTRIBUTE_TAGGED_ITERATOR_WITH_FULL_SIGNATURE,
                            'tag',
                            '',
                            '\\'.IteratorFormat::class.'::ArrayWithCustomKey',
                            sprintf(
                                "[%s::class => 'class2', %s::class => 'class3']",
                                self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                                self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            ),
                        ),
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
                    ->setPrefix('interface')
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'tag')]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
        ];

        $config = $this->generateConfig(includedPaths: $classes);

        $container = (new ContainerBuilder())->add($config)->build();

        $composites = [
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
        ];

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);

        foreach ($composites as $composite) {
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
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithNonSingletonDependenciesAsTaggedIterator(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        $classImplementingName1 = ClassGenerator::getClassName();
        $classImplementingName2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'Interface1')])
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classImplementingName1.php")
                    ->setName($classImplementingName1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName])
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classImplementingName2.php")
                    ->setName($classImplementingName2)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName])
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE, 'Interface1'),
                        'public readonly iterable $dependency,',
                    ])
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$classImplementingName1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$classImplementingName2.'.php',
        ];

        $config = $this->generateConfig(includedPaths: $classes);

        $container = (new ContainerBuilder())->add($config)->build();

        $collectorClass = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);
        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$classImplementingName1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$classImplementingName2);

        self::assertInstanceOf($class1::class, $collectorClass->dependency[0]);
        self::assertInstanceOf($class2::class, $collectorClass->dependency[1]);

        self::assertNotSame(
            $collectorClass->dependency[0],
            $class1,
        );
        self::assertNotSame(
            $collectorClass->dependency[1],
            $class2,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithTaggedInterfaceImplementation(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        $classImplementingName1 = ClassGenerator::getClassName();
        $classImplementingName2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'Interface1')])
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classImplementingName1.php")
                    ->setName($classImplementingName1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classImplementingName2.php")
                    ->setName($classImplementingName2)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE, 'Interface1'),
                        'public readonly iterable $dependency,',
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$classImplementingName1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$classImplementingName2.'.php',
        ];

        $config = $this->generateConfig(includedPaths: $classes);

        $container = (new ContainerBuilder())->add($config)->build();

        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);
        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$classImplementingName1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$classImplementingName2);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorClassName, $collector);
        self::assertCount(2, $collector->dependency);

        self::assertInstanceOf($class1::class, $collector->dependency[0]);
        self::assertInstanceOf($class2::class, $collector->dependency[1]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classImplementingName1, $collector->dependency[0]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classImplementingName2, $collector->dependency[1]);

        self::assertSame($collector->dependency[0], $class1);

        self::assertSame($collector->dependency[1], $class2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithTaggedIterator(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $taggedClassName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE, 'tag_1'),
                        'public readonly array $dependency1,',
                        sprintf(self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE, 'tag_1'),
                        'public readonly iterable $dependency2,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$taggedClassName.php")
                    ->setName($taggedClassName)
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'tag_1')])
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'tag_1')]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$taggedClassName.'.php',
        ];

        $config = $this->generateConfig(includedPaths: $classes);

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorClassName, $class);
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithTaggedIteratorWithExcludedClass(): void
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
                    ->setPrefix('interface')
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'interface tag'),
                    ]),
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
                        sprintf(
                            self::ATTRIBUTE_TAGGED_ITERATOR_WITH_EXCLUDE_SIGNATURE,
                            'interface tag',
                            'self::class',
                        ),
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
                        sprintf(
                            self::ATTRIBUTE_TAGGED_ITERATOR_WITH_EXCLUDE_SIGNATURE,
                            'interface tag',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$composite.'::class',
                        ),
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
                        sprintf(
                            self::ATTRIBUTE_TAGGED_ITERATOR_WITH_FULL_SIGNATURE,
                            'tag',
                            '',
                            '\\'.IteratorFormat::class.'::ArrayWithCustomKey',
                            sprintf("[%s::class => 'test']", self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        ),
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
                    ->setPrefix('interface')
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'tag')]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
        ];

        $config = $this->generateConfig(includedPaths: $classes);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Missing class "%s" in "customFormatMapping".', self::GENERATED_CLASS_NAMESPACE.$className2),
        );

        (new ContainerBuilder())->add($config)->build();
    }
}

<?php

declare(strict_types=1);

namespace Container\Config;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Container\Attribute\Bind\TaggedIterator;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;
use Temkaa\Container\Exception\UnresolvableArgumentException;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

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
    public function testCompilesWithConfigPrecedence(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $interfaceName1 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName1.php")
                    ->setName($interfaceName1)
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'interface_tag')])
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE,
                            'non_interface_tag',
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
                        '$dependency' => new TaggedIterator('interface_tag'),
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
                        '$list'                  => new TaggedIterator(
                            'tag',
                        ),
                        '$arrayWithNamespaceKey' => new TaggedIterator(
                            'tag',
                            format: IteratorFormat::ArrayWithClassNamespaceKey,
                        ),
                        '$arrayWithClassNameKey' => new TaggedIterator(
                            'tag',
                            format: IteratorFormat::ArrayWithClassNameKey,
                        ),
                        '$arrayWithCustomKey'    => new TaggedIterator(
                            'tag',
                            format: IteratorFormat::ArrayWithCustomKey,
                            customFormatMapping: [
                                self::GENERATED_CLASS_NAMESPACE.$className2 => 'class2',
                                self::GENERATED_CLASS_NAMESPACE.$className3 => 'class3',
                            ],
                        ),
                    ],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$interfaceName,
                    tags: ['tag'],
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
                        'public readonly array $dependency1,',
                        'public readonly iterable $dependency2,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$taggedClassName.php")
                    ->setName($taggedClassName),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$taggedClassName.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: [
                        'dependency1' => new TaggedIterator('empty_2'),
                        'dependency2' => new TaggedIterator('empty_2'),
                    ],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$taggedClassName,
                    tags: ['empty_2'],
                ),
            ],
        );

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
    public function testCompilesWithTaggedIteratorFromAbstractClass(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $abstractClass = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$abstractClass.php")
                    ->setName($abstractClass)
                    ->setPrefix('abstract class'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass]),
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
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$abstractClass,
                    tags: ['abstract'],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: ['dependency' => new TaggedIterator('abstract')],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $collectorClass = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertSame(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
            ],
            $collectorClass->dependency,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithTaggedIteratorFromInterface(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
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
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$interface,
                    tags: ['interface'],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: ['dependency' => new TaggedIterator('interface')],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $collectorClass = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertSame(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
            ],
            $collectorClass->dependency,
        );
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
                        '$handlers' => new TaggedIterator(
                            'interface tag',
                            exclude: [self::GENERATED_CLASS_NAMESPACE.$composite],
                        ),
                    ],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className3,
                    variableBindings: [
                        '$handlers' => new TaggedIterator(
                            'interface tag',
                            exclude: [self::GENERATED_CLASS_NAMESPACE.$composite],
                        ),
                    ],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$interfaceName,
                    tags: ['interface tag'],
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
                        '$dependency' => new TaggedIterator(
                            'tag',
                            format: IteratorFormat::ArrayWithCustomKey,
                            customFormatMapping: [
                                self::GENERATED_CLASS_NAMESPACE.$className3 => 'test',
                            ],
                        ),
                    ],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$interfaceName,
                    tags: ['tag'],
                ),
            ],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Missing class "%s" in "customFormatMapping".', self::GENERATED_CLASS_NAMESPACE.$className2),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToUnsupportedArgumentTypeFromConfig(): void
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
                        'public readonly string $dependency1,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$taggedClassName.php")
                    ->setName($taggedClassName),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$taggedClassName.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: [
                        'dependency1' => new TaggedIterator('empty_2'),
                    ],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$taggedClassName,
                    tags: ['empty_2'],
                ),
            ],
        );

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with tagged argument "dependency1::string" as it\'s type is neither "array" or "iterable".',
                self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }
}

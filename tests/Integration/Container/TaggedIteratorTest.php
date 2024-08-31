<?php

declare(strict_types=1);

namespace Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Exception\CircularReferenceException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

/**
 * @psalm-suppress ArgumentTypeCoercion, MixedAssignment, MixedArrayAccess, MixedPropertyFetch, MixedArgument
 */
final class TaggedIteratorTest extends AbstractContainerTestCase
{
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
                        sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'Interface1'),
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
                        sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'Interface1'),
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

        /** @psalm-suppress PossiblyInvalidArrayAccess, UndefinedInterfaceMethod */
        self::assertInstanceOf($class1::class, $collector->dependency[0]);
        /** @psalm-suppress PossiblyInvalidArrayAccess, UndefinedInterfaceMethod */
        self::assertInstanceOf($class2::class, $collector->dependency[1]);
        /** @psalm-suppress PossiblyInvalidArrayAccess, UndefinedInterfaceMethod */
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classImplementingName1, $collector->dependency[0]);
        /** @psalm-suppress PossiblyInvalidArrayAccess, UndefinedInterfaceMethod */
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classImplementingName2, $collector->dependency[1]);

        /** @psalm-suppress PossiblyInvalidArrayAccess, UndefinedInterfaceMethod */
        self::assertSame($collector->dependency[0], $class1);

        /** @psalm-suppress PossiblyInvalidArrayAccess, UndefinedInterfaceMethod */
        self::assertSame($collector->dependency[1], $class2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithTaggedIteratorFromAttribute(): void
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
                        sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'tag_1'),
                        'public readonly array $dependency1,',
                        sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'tag_1'),
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
    public function testCompilesWithTaggedIteratorFromConfig(): void
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
                        'dependency1' => '!tagged empty_2',
                        'dependency2' => '!tagged empty_2',
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
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToCircularExceptionByTaggedBinding(): void
    {
        // TODO: write test on tagging an interface from config
        // TODO: add badge with code coverage
        // TODO: add badge with infection score
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'circular')])
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'circular'),
                        'public readonly iterable $arg',
                    ]),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];

        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(CircularReferenceException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate class "%s" as it has circular references "%s".',
                $className,
                $className,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToTaggedBindingToNonIterableArgumentType(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'non_iterable_tag'),
                        'public readonly string $arg',
                    ]),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];

        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with tagged argument "arg::string" as it\'s type is neither "array" or "iterable".',
                self::GENERATED_CLASS_NAMESPACE.$className,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }
}

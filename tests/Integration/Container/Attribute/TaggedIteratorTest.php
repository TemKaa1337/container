<?php

declare(strict_types=1);

namespace Container\Attribute;

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
}

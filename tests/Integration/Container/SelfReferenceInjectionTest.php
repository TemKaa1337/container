<?php

declare(strict_types=1);

namespace Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Container;
use Temkaa\Container\Exception\EntryNotFoundException;
use Temkaa\Container\Model\Config\Decorator;
use Temkaa\Container\Model\Definition\Bag;
use Temkaa\Container\Model\Definition\ClassDefinition;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

/**
 * @psalm-suppress MixedAssignment, MixedPropertyFetch, InternalMethod, ArgumentTypeCoercion
 * @psalm-suppress MixedArgument, MixedMethodCall, InternalClass
 */
final class SelfReferenceInjectionTest extends AbstractContainerTestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInjectedContainerIntoClassConstructorsByClass(): void
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
                        sprintf('public readonly %s $container,', '\\'.Container::class),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $container,', '\\'.Container::class),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $container,', '\\'.Container::class),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class3);

        self::assertSame($container, $class1->container);
        self::assertSame($container, $class2->container);
        self::assertSame($container, $class3->container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInjectedContainerIntoClassConstructorsByInterface(): void
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
                        sprintf('public readonly ?%s $container = null,', '\\'.ContainerInterface::class),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly ?%s $container = null,', '\\'.ContainerInterface::class),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly ?%s $container = null,', '\\'.ContainerInterface::class),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class3);

        self::assertSame($container, $class1->container);
        self::assertSame($container, $class2->container);
        self::assertSame($container, $class3->container);

        self::assertNotNull($class1->container);
        self::assertNotNull($class2->container);
        self::assertNotNull($class3->container);

        self::assertInstanceOf(ContainerInterface::class, $class1->container);
        self::assertInstanceOf(ContainerInterface::class, $class2->container);
        self::assertInstanceOf(ContainerInterface::class, $class3->container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInjectedContainerIntoSingletonClassConstructorsFromAttributeByClass(): void
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
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $container,', '\\'.ContainerInterface::class),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $container,', '\\'.ContainerInterface::class),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_AUTOWIRE_SIGNATURE,
                            'true',
                            'false',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $container,', '\\'.ContainerInterface::class),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $class1Retrieve1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2Retrieve1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3Retrieve1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);

        $class1Retrieve2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2Retrieve2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3Retrieve2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);

        self::assertNotSame($class1Retrieve1, $class1Retrieve2);
        self::assertNotSame($class2Retrieve1, $class2Retrieve2);
        self::assertNotSame($class3Retrieve1, $class3Retrieve2);

        self::assertSame($container, $class1Retrieve1->container);
        self::assertSame($container, $class1Retrieve2->container);
        self::assertSame($container, $class2Retrieve1->container);
        self::assertSame($container, $class2Retrieve2->container);
        self::assertSame($container, $class3Retrieve1->container);
        self::assertSame($container, $class3Retrieve2->container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInjectedContainerIntoSingletonClassConstructorsFromConfigByClass(): void
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
                        sprintf('public readonly %s $container,', '\\'.ContainerInterface::class),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $container,', '\\'.ContainerInterface::class),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $container,', '\\'.ContainerInterface::class),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
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
                    className: self::GENERATED_CLASS_NAMESPACE.$className3,
                    singleton: false,
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1Retrieve1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2Retrieve1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3Retrieve1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);

        $class1Retrieve2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2Retrieve2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3Retrieve2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);

        self::assertNotSame($class1Retrieve1, $class1Retrieve2);
        self::assertNotSame($class2Retrieve1, $class2Retrieve2);
        self::assertNotSame($class3Retrieve1, $class3Retrieve2);

        self::assertSame($container, $class1Retrieve1->container);
        self::assertSame($container, $class1Retrieve2->container);
        self::assertSame($container, $class2Retrieve1->container);
        self::assertSame($container, $class2Retrieve2->container);
        self::assertSame($container, $class3Retrieve1->container);
        self::assertSame($container, $class3Retrieve2->container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithRewrittenClassConfig(): void
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
                        sprintf(self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE, 'container_tag'),
                        'public readonly iterable $tagged,',
                        sprintf('public readonly %s $inner,', '\\'.ContainerInterface::class),
                    ])
                    ->setInterfaceImplementations(['\\'.ContainerInterface::class])
                    ->setBody([
                        'public function get(string $id): null {return null;}',
                        'public function has(string $id): bool {return true;}',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $container,', '\\'.ContainerInterface::class),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.'/../../../src/Container.php',
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: Container::class,
                    aliases: ['container_config_alias'],
                    singleton: false,
                    tags: ['container_tag'],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    decorates: new Decorator(
                        id: ContainerInterface::class,
                    ),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        self::assertSame($container->get(ContainerInterface::class), $container->get(ContainerInterface::class));
        self::assertFalse($container->has('container_config_alias'));

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertEmpty($class->tagged);
        self::assertSame($container, $class->inner);
        self::assertSame($class, $container->get(ContainerInterface::class));
        self::assertTrue($container->get(ContainerInterface::class)->has('non_existent_class'));

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertSame($class, $class2->container);
        self::assertTrue($class2->container->has('some_alias'));

        $reflection = new ReflectionClass($container);
        $definitionRepository = $reflection->getProperty('definitionRepository')->getValue($container);
        $reflection = new ReflectionClass($definitionRepository);

        /** @var Bag $definitions */
        $definitions = $reflection->getProperty('definitions')->getValue($definitionRepository);

        /** @var ClassDefinition $containerDefinition */
        $containerDefinition = $definitions->get(Container::class);
        self::assertEmpty($containerDefinition->getArguments());
        self::assertEmpty($containerDefinition->getTags());
        self::assertEquals(['container'], $containerDefinition->getAliases());
        self::assertEquals([ContainerInterface::class], $containerDefinition->getImplements());
        self::assertEquals(Container::class, $containerDefinition->getId());
        self::assertSame($container, $containerDefinition->getInstance());
        self::assertTrue($containerDefinition->isSingleton());

        $this->expectException(EntryNotFoundException::class);
        $container->get('container_config_alias');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetByAlias(): void
    {
        $container = ContainerBuilder::make()->build();

        self::assertSame($container, $container->get('container'));
        self::assertSame($container->get('container'), $container->get('container'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetByInterface(): void
    {
        $container = ContainerBuilder::make()->build();

        self::assertSame($container, $container->get(ContainerInterface::class));
        self::assertSame($container->get(ContainerInterface::class), $container->get(ContainerInterface::class));
    }
}

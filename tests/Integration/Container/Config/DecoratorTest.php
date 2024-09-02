<?php

declare(strict_types=1);

namespace Container\Config;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Model\Config\Decorator;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

/**
 * @psalm-suppress ArgumentTypeCoercion, MixedPropertyFetch, MixedAssignment
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
    public function testCompilesWithDecoratorFromConfig(): void
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
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className2,
                    decorates: new Decorator(
                        self::GENERATED_CLASS_NAMESPACE.$className1,
                        signature: '$dependency',
                    ),
                ),
            ],
        );

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
    public function testCompilesWithDecoratorWithoutDecoratedServiceInjectedFromConfig(): void
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
                    ->setName($className2),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className2,
                    decorates: new Decorator(id: self::GENERATED_CLASS_NAMESPACE.$className1),
                ),
            ],
        );

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
    public function testCompilesWithDifferentInterfaceImplementationsAndOnlyOneDecorator(): void
    {
        $interfaceName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
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
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName => self::GENERATED_CLASS_NAMESPACE.$className1,
            ],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className4,
                    decorates: new Decorator(id: self::GENERATED_CLASS_NAMESPACE.$interfaceName),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $decorator = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName);
        self::assertSame($container->get(self::GENERATED_CLASS_NAMESPACE.$className4), $decorator);
        self::assertSame($container->get(self::GENERATED_CLASS_NAMESPACE.$className1), $decorator->decorated);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithMultipleDecoratorsByClassWithOneDecoratedPropertyDeclared(): void
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
                            'dependency',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $propertyName',
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
                            'dependency',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $otherPropertyName',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $lastPropertyName',
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
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className4,
                    decorates: new Decorator(
                        id: self::GENERATED_CLASS_NAMESPACE.$className1,
                        priority: 1,
                    ),
                ),
            ],
        );

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
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $decorated->lastPropertyName);
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $decorated->lastPropertyName->otherPropertyName,
        );
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $decorated->lastPropertyName->otherPropertyName->propertyName,
        );
    }
}

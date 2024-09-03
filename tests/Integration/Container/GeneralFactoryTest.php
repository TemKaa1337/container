<?php

declare(strict_types=1);

namespace Tests\Integration\Container;

use Generator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Exception\ClassFactoryException;
use Temkaa\SimpleContainer\Exception\Config\EntryNotFoundException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Model\Config\Factory;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;

/**
 * @SuppressWarnings(PHPMD)
 *
 * @psalm-suppress ArgumentTypeCoercion, MixedPropertyFetch, MixedAssignment, MixedArgumentTypeCoercion, UndefinedClass
 */
final class GeneralFactoryTest extends AbstractContainerTestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToFactoryDoesNotHaveReturnType(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public static function create()
                            {
                                return new %s($this);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Factory method "%s::create" for class "%s" must have an explicit non-union and non-intersection type, got "".',
                self::GENERATED_CLASS_NAMESPACE.$className2,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToFactoryReturnsOtherObject(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public static function create(): %s
                            {
                                return new %s($this);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Factory method "%s::create" for class "%s" must return compatible instance, got "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className2,
                self::GENERATED_CLASS_NAMESPACE.$className1,
                self::GENERATED_CLASS_NAMESPACE.$className2,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToNonExistingFactoryClassFromAttribute(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            'SomeNonExistingClass',
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(EntryNotFoundException::class);
        $this->expectExceptionMessage('Class "SomeNonExistingClass" not found.');

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToNonExistingFactoryClassFromConfig(): void
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
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    factory: new Factory(id: 'SomeNonExistingClass', method: 'create', boundedVariables: []),
                ),
            ],
        );

        $this->expectException(EntryNotFoundException::class);
        $this->expectExceptionMessage('Class "SomeNonExistingClass" not found.');

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToNonExistingFactoryMethodFromAttribute(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
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

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not find method named "%s" in class "%s" for factory of class "%s".',
                'create',
                self::GENERATED_CLASS_NAMESPACE.$className3,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToNonExistingFactoryMethodFromConfig(): void
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
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
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

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    factory: new Factory(
                        id: self::GENERATED_CLASS_NAMESPACE.$className3,
                        method: 'create',
                        boundedVariables: [],
                    ),
                ),
            ],
        );

        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not find method named "%s" in class "%s" for factory of class "%s".',
                'create',
                self::GENERATED_CLASS_NAMESPACE.$className3,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWhenClassWithFactoryIsDecoratorWithMultipleFactoryArgumentsAndNoDecoratorNamedVariable(
    ): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName().'interface';
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className2,
                            'create',
                        ),
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                            'decorated',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $var1,',
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        'public readonly int $var2,',
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s string $var1, %s $priorityTwoDecorator, %s int $var2): %s
                            {
                                return new %s($var1, $priorityTwoDecorator, $var2);
                            }
                            METHOD,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'string'),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, '1'),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            2,
                            'dependency',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                            'dependency',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $decorated,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className5.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not resolve decorated class in class "%s" as it does not have argument named "decorated".',
                self::GENERATED_CLASS_NAMESPACE.$className2,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWhenFactoryMethodIsNotAccessible(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setPrefix('abstract class')
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public function create(): %s
                            {
                                return new %s();
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Factory method "%s::create" for class "%s" must be instantiable.',
                self::GENERATED_CLASS_NAMESPACE.$className2,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );
        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithInternalClassAsFactoryClass(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            Generator::class,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public static function create(): %s
                            {
                                return new %s($this);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Factory method "Generator::create" for class "%s" cannot not be internal.',
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithPrivateConstructorAndSeparateFactoryClass(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorVisibility('private')
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            'create',
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public function create(): %s
                            {
                                return new %s();
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Invalid factory method "%s::create" for class "%s", as class "%s" has inaccessible constructor.',
                self::GENERATED_CLASS_NAMESPACE.$className2,
                self::GENERATED_CLASS_NAMESPACE.$className1,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );
        (new ContainerBuilder())->add($config)->build();
    }
}

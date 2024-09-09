<?php

declare(strict_types=1);

namespace Tests\Integration\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;

/**
 * @psalm-suppress MixedAssignment, MixedPropertyFetch
 */
final class BoundVariableWithDefaultValueTest extends AbstractContainerTestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithBuiltInArgumentWithDefaultNonNullValue(): void
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
                        'public readonly string $arg = "some_string"',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorVisibility('private')
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setConstructorArguments([
                        'public readonly array $arg',
                    ])
                    ->setBody([
                        <<<'METHOD'
                            public static function create(array $arg = ['a']): self
                            {
                                return new self($arg);
                            }
                        METHOD,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setBody([
                        'public int $arg;',
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        <<<'METHOD'
                            public function set(int $arg = 10): void
                            {
                                $this->arg = $arg;
                            }
                        METHOD,
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertSame('some_string', $class1->arg);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertSame(['a'], $class2->arg);

        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        self::assertSame(10, $class3->arg);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithBuiltInArgumentWithDefaultNullValue(): void
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
                        'public readonly ?string $arg = null',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorVisibility('private')
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setConstructorArguments([
                        'public readonly ?array $arg',
                    ])
                    ->setBody([
                        <<<'METHOD'
                            public static function create(?array $arg = null): self
                            {
                                return new self($arg);
                            }
                        METHOD,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setBody([
                        'public ?int $arg;',
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        <<<'METHOD'
                            public function set(?int $arg = null): void
                            {
                                $this->arg = $arg;
                            }
                        METHOD,
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertNull($class1->arg);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertNull($class2->arg);

        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        self::assertNull($class3->arg);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDefaultValueOfAbstractClass(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg = new %s(["a"]),',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className5,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorVisibility('private')
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                                public static function create(%s $arg = new %s(['a'])): self
                                {
                                    return new self($arg);
                                }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className5,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setBody([
                        sprintf('public %s $arg;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                                public function set(%s $arg = new %s(['a'])): void
                                {
                                    $this->arg = $arg;
                                }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className5,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setPrefix('abstract class'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5)
                    ->setHasConstructor(true)
                    ->setConstructorArguments(['public readonly iterable $arg,'])
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class1->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className5, $class1->arg);
        self::assertSame(['a'], $class1->arg->arg);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class2->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className5, $class2->arg);
        self::assertSame(['a'], $class2->arg->arg);

        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class3->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className5, $class3->arg);
        self::assertSame(['a'], $class3->arg->arg);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDefaultValueOfRegularClassWhichIsInExcludedSection(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg = new %s(["a"]),',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorVisibility('private')
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                                public static function create(%s $arg = new %s(['a'])): self
                                {
                                    return new self($arg);
                                }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setBody([
                        sprintf('public %s $arg;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                                public function set(%s $arg = new %s(['a'])): void
                                {
                                    $this->arg = $arg;
                                }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setConstructorArguments(['public readonly iterable $arg,']),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            ],
            excludedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className4.'.php',
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class1->arg);
        self::assertSame(['a'], $class1->arg->arg);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class2->arg);
        self::assertSame(['a'], $class2->arg->arg);

        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class3->arg);
        self::assertSame(['a'], $class3->arg->arg);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDefaultValueOfRegularClassWhichIsNonAutowirable(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg = new %s(["a"]),',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorVisibility('private')
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                                public static function create(%s $arg = new %s(['a'])): self
                                {
                                    return new self($arg);
                                }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setBody([
                        sprintf('public %s $arg;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                                public function set(%s $arg = new %s(['a'])): void
                                {
                                    $this->arg = $arg;
                                }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'false', 'true'),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments(['public readonly iterable $arg,']),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class1->arg);
        self::assertSame(['a'], $class1->arg->arg);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class2->arg);
        self::assertSame(['a'], $class2->arg->arg);

        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class3->arg);
        self::assertSame(['a'], $class3->arg->arg);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInterfaceDefaultArgumentWhichIsAutoDiscovered(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg = new %s(["a"]),',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorVisibility('private')
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                                public static function create(%s $arg = new %s(['a'])): self
                                {
                                    return new self($arg);
                                }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setBody([
                        sprintf('public %s $arg;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                                public function set(%s $arg = new %s(['a'])): void
                                {
                                    $this->arg = $arg;
                                }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className4.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$interface.'.php',
            ],
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

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class1->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class1->arg);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class2->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class2->arg);

        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class3->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class3->arg);

        self::assertNotSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
        );
        self::assertNotSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
        );
        self::assertNotSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
        );

        self::assertSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1)->arg,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1)->arg,
        );
        self::assertSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2)->arg,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2)->arg,
        );
        self::assertSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3)->arg,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3)->arg,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInterfaceDefaultArgumentWhichIsBoundedInConfig(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg = new %s(["a"]),',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorVisibility('private')
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                                public static function create(%s $arg = new %s(['a'])): self
                                {
                                    return new self($arg);
                                }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setBody([
                        sprintf('public %s $arg;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                                public function set(%s $arg = new %s(['a'])): void
                                {
                                    $this->arg = $arg;
                                }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            ],
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interface => self::GENERATED_CLASS_NAMESPACE.$className4,
            ],
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

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class1->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class1->arg);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class2->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class2->arg);

        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class3->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class3->arg);

        self::assertNotSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
        );
        self::assertNotSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
        );
        self::assertNotSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
        );

        self::assertSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1)->arg,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1)->arg,
        );
        self::assertSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2)->arg,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2)->arg,
        );
        self::assertSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3)->arg,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3)->arg,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInterfaceDefaultArgumentWhichIsNotLoaded(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg = new %s(["a"]),',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorVisibility('private')
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                                public static function create(%s $arg = new %s(['a'])): self
                                {
                                    return new self($arg);
                                }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setBody([
                        sprintf('public %s $arg;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                                public function set(%s $arg = new %s(['a'])): void
                                {
                                    $this->arg = $arg;
                                }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            ],
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

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class1->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class1->arg);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class2->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class2->arg);

        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class3->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class3->arg);

        self::assertNotSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
        );
        self::assertNotSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
        );
        self::assertNotSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
        );

        self::assertSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1)->arg,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1)->arg,
        );
        self::assertSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2)->arg,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2)->arg,
        );
        self::assertSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3)->arg,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3)->arg,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInternalObjectWithDefaultValue(): void
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
                        'public readonly \ReflectionClass $arg = new \ReflectionClass(self::class),',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorVisibility('private')
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className2,
                            'create',
                        ),
                    ])
                    ->setConstructorArguments([
                        'public readonly \ReflectionClass $arg',
                    ])
                    ->setBody([
                        <<<'METHOD'
                            public static function create(\ReflectionClass $arg = new \ReflectionClass(self::class)): self
                            {
                                return new self($arg);
                            }
                        METHOD,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setBody([
                        'public \ReflectionClass $arg;',
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        <<<'METHOD'
                            public function set(\ReflectionClass $arg = new \ReflectionClass(self::class)): void
                            {
                                $this->arg = $arg;
                            }
                        METHOD,
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(ReflectionClass::class, $class1->arg);
        self::assertSame($class1->arg->getName(), $class1::class);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(ReflectionClass::class, $class2->arg);
        self::assertSame($class2->arg->getName(), $class2::class);

        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        self::assertInstanceOf(ReflectionClass::class, $class3->arg);
        self::assertSame($class3->arg->getName(), $class3::class);
    }
}

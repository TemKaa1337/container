<?php

declare(strict_types=1);

namespace Container\Config;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Container\Attribute\Bind\Instance;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Exception\ClassNotFoundException;
use Temkaa\Container\Exception\UnresolvableArgumentException;
use Temkaa\Container\Model\Config\Factory;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;
use function sprintf;

/**
 * @psalm-suppress all
 * @SuppressWarnings(PHPMD)
 */
final class InstanceTest extends AbstractContainerTestCase
{
    /**
     * @psalm-suppress InvalidClassConstantType
     */
    protected const string GENERATED_CLASS_STUB_PATH = '/../../../Fixture/Stub/Class/';

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws NotFoundExceptionInterface
     */
    public function testCompilesWithConfigPrecedence(): void
    {
        $collector = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
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
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collector.php")
                    ->setName($collector)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_INSTANCE_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1.'::class',
                        ),
                        sprintf('public readonly %s $instance,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$collector.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interface.php",
        ];

        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collector,
                    variableBindings: [
                        '$instance' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className2),
                    ],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$collector);
        self::assertSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
            $class->instance,
        );
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithConstructorInjection(): void
    {
        $abstractClass1 = ClassGenerator::getClassName();
        $abstractClass2 = ClassGenerator::getClassName(); // extends 1
        $interface1 = ClassGenerator::getClassName();
        $interface2 = ClassGenerator::getClassName(); // extends 1

        $collectorClassName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName(); // extends abstract 1
        $className2 = ClassGenerator::getClassName(); // extends abstract 2
        $className3 = ClassGenerator::getClassName(); // implements interface 1
        $className4 = ClassGenerator::getClassName(); // implements interface 2

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$abstractClass1.php")
                    ->setName($abstractClass1)
                    ->setPrefix('abstract class'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$abstractClass2.php")
                    ->setName($abstractClass2)
                    ->setPrefix('abstract class')
                    ->setExtends([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface1.php")
                    ->setName($interface1)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface2.php")
                    ->setName($interface2)
                    ->setPrefix('interface')
                    ->setExtends([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setExtends([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setExtends([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass2,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface2,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $var1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1),
                        sprintf('public readonly %s $var2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass2),
                        sprintf('public readonly %s $var3,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1),
                        sprintf('public readonly %s $var4,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1),
                        sprintf('public readonly %s $var5,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface2),
                        sprintf('public readonly %s $var6,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1),
                        sprintf('public readonly %s $var7,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1),
                        'public readonly object $var8,',
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$abstractClass1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$abstractClass2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interface1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interface2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className4.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: [
                        'var1' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className1),
                        'var2' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className2),
                        'var3' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className2),
                        'var4' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className3),
                        'var5' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className4),
                        'var6' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className4),
                        'var7' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className1),
                        'var8' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className1),
                    ],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        $class4 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);

        self::assertSame($class1, $collector->var1);
        self::assertSame($class2, $collector->var2);
        self::assertSame($class2, $collector->var3);
        self::assertSame($class3, $collector->var4);
        self::assertSame($class4, $collector->var5);
        self::assertSame($class4, $collector->var6);
        self::assertSame($class1, $collector->var7);
        self::assertSame($class1, $collector->var8);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithFactoryInjection(): void
    {
        $abstractClass1 = ClassGenerator::getClassName();
        $abstractClass2 = ClassGenerator::getClassName(); // extends 1
        $interface1 = ClassGenerator::getClassName();
        $interface2 = ClassGenerator::getClassName(); // extends 1

        $collectorClassName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName(); // extends abstract 1
        $className2 = ClassGenerator::getClassName(); // extends abstract 2
        $className3 = ClassGenerator::getClassName(); // implements interface 1
        $className4 = ClassGenerator::getClassName(); // implements interface 2

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$abstractClass1.php")
                    ->setName($abstractClass1)
                    ->setPrefix('abstract class'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$abstractClass2.php")
                    ->setName($abstractClass2)
                    ->setPrefix('abstract class')
                    ->setExtends([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface1.php")
                    ->setName($interface1)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface2.php")
                    ->setName($interface2)
                    ->setPrefix('interface')
                    ->setExtends([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setExtends([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setExtends([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass2,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface2,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $var1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1),
                        sprintf('public readonly %s $var2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass2),
                        sprintf('public readonly %s $var3,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1),
                        sprintf('public readonly %s $var4,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1),
                        sprintf('public readonly %s $var5,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface2),
                        sprintf('public readonly %s $var6,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1),
                        sprintf('public readonly %s $var7,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1),
                        'public readonly object $var8,',
                    ])
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s $var1, %s $var2, %s $var3, %s $var4, %s $var5, %s $var6, %s $var7, %s $var8): self
                            {
                                return new self($var1, $var2, $var3, $var4, $var5, $var6, $var7, $var8);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass2,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface2,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            'object',
                        ),
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$abstractClass1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$abstractClass2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interface1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interface2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className4.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    factory: new Factory(
                        self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                        method: 'create',
                        boundedVariables: [
                            'var1' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className1),
                            'var2' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className2),
                            'var3' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className2),
                            'var4' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className3),
                            'var5' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className4),
                            'var6' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className4),
                            'var7' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className1),
                            'var8' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className1),
                        ],
                    ),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        $class4 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);

        self::assertSame($class1, $collector->var1);
        self::assertSame($class2, $collector->var2);
        self::assertSame($class2, $collector->var3);
        self::assertSame($class3, $collector->var4);
        self::assertSame($class4, $collector->var5);
        self::assertSame($class4, $collector->var6);
        self::assertSame($class1, $collector->var7);
        self::assertSame($class1, $collector->var8);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithRequiredMethodInjection(): void
    {
        $abstractClass1 = ClassGenerator::getClassName();
        $abstractClass2 = ClassGenerator::getClassName(); // extends 1
        $interface1 = ClassGenerator::getClassName();
        $interface2 = ClassGenerator::getClassName(); // extends 1

        $collectorClassName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName(); // extends abstract 1
        $className2 = ClassGenerator::getClassName(); // extends abstract 2
        $className3 = ClassGenerator::getClassName(); // implements interface 1
        $className4 = ClassGenerator::getClassName(); // implements interface 2

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$abstractClass1.php")
                    ->setName($abstractClass1)
                    ->setPrefix('abstract class'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$abstractClass2.php")
                    ->setName($abstractClass2)
                    ->setPrefix('abstract class')
                    ->setExtends([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface1.php")
                    ->setName($interface1)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface2.php")
                    ->setName($interface2)
                    ->setPrefix('interface')
                    ->setExtends([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setExtends([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setExtends([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass2,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface2,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setBody([
                        sprintf('public readonly %s $var1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1),
                        sprintf('public readonly %s $var2;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass2),
                        sprintf('public readonly %s $var3;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1),
                        sprintf('public readonly %s $var4;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1),
                        sprintf('public readonly %s $var5;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface2),
                        sprintf('public readonly %s $var6;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1),
                        sprintf('public readonly %s $var7;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1),
                        'public readonly object $var8;',
                        sprintf(
                            <<<'METHOD'
                            public function setProps(%s $var1, %s $var2, %s $var3, %s $var4, %s $var5, %s $var6, %s $var7, %s $var8): void
                            {
                                $this->var1 = $var1;
                                $this->var2 = $var2;
                                $this->var3 = $var3;
                                $this->var4 = $var4;
                                $this->var5 = $var5;
                                $this->var6 = $var6;
                                $this->var7 = $var7;
                                $this->var8 = $var8;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass2,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$abstractClass1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface2,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            'object',
                        ),
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$abstractClass1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$abstractClass2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interface1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interface2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className4.'.php',
        ];
        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: [
                        'var1' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className1),
                        'var2' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className2),
                        'var3' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className2),
                        'var4' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className3),
                        'var5' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className4),
                        'var6' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className4),
                        'var7' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className1),
                        'var8' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className1),
                    ],
                    requiredMethodCalls: ['setProps'],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        $class4 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);

        $this->assertInitialized($collector, 'var1');
        $this->assertInitialized($collector, 'var2');
        $this->assertInitialized($collector, 'var3');
        $this->assertInitialized($collector, 'var4');
        $this->assertInitialized($collector, 'var5');
        $this->assertInitialized($collector, 'var6');
        $this->assertInitialized($collector, 'var7');
        $this->assertInitialized($collector, 'var8');
        self::assertSame($class1, $collector->var1);
        self::assertSame($class2, $collector->var2);
        self::assertSame($class2, $collector->var3);
        self::assertSame($class3, $collector->var4);
        self::assertSame($class4, $collector->var5);
        self::assertSame($class4, $collector->var6);
        self::assertSame($class1, $collector->var7);
        self::assertSame($class1, $collector->var8);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithMissingClassInContainer(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
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
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $class,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1),
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: [
                        'class' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className1),
                    ],
                ),
            ],
        );

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf('Class "%s" is not found.', self::GENERATED_CLASS_NAMESPACE.$className1),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithWrongType(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $class,',
                    ]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classes,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                    variableBindings: [
                        'class' => new Instance(self::GENERATED_CLASS_NAMESPACE.$className1),
                    ],
                ),
            ],
        );

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with instance argument "class::string" as it\'s type '
                .'is not subtype of bounded instance: "%s".',
                self::GENERATED_CLASS_NAMESPACE.$collectorClassName,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }
}

<?php

declare(strict_types=1);

namespace Container\Config;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Attribute\Bind\TaggedIterator;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Model\Config\Decorator;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

/**
 * @psalm-suppress ArgumentTypeCoercion, MixedPropertyFetch, MixedAssignment, MixedArgument, InternalClass,
 *                 InternalMethod
 */
final class RequiredTest extends AbstractContainerTestCase
{
    /**
     * @psalm-suppress InvalidClassConstantType
     */
    protected const string GENERATED_CLASS_STUB_PATH = '/../../../Fixture/Stub/Class/';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithBindingDecorator(): void
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
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        <<<'METHOD'
                        public function set(string $nonBoundString): void
                        {
                            throw new \Exception($nonBoundString);
                        }
                        METHOD,
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1): void
                            {
                                if (isset($this->arg1)) {
                                    throw new \Exception();
                                }
                                
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
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

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    requiredMethodCalls: ['setArg1', 'setArg1'],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className2,
                    decorates: new Decorator(
                        self::GENERATED_CLASS_NAMESPACE.$interface,
                        0,
                    ),
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className3,
                    decorates: new Decorator(
                        self::GENERATED_CLASS_NAMESPACE.$interface,
                        1,
                    ),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        $this->assertInitialized($object, 'arg1');

        $decorator = $container->get(self::GENERATED_CLASS_NAMESPACE.$interface);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $decorator);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $decorator);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $decorator->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $decorator->arg1->arg1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $object->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $object->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $object->arg1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $object->arg1->arg1->arg1);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCompilesWithMultipleRequiredMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        sprintf(
                            <<<'METHOD'
                            public function setArg2(%s $arg2): void
                            {
                                $this->arg2 = $arg2;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
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
                    requiredMethodCalls: ['setArg1', 'setArg2'],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        $this->assertInitialized($object, 'arg1');
        $this->assertInitialized($object, 'arg2');

        self::assertSame($object->arg1, $container->get(self::GENERATED_CLASS_NAMESPACE.$className2));
        self::assertSame($object->arg2, $container->get(self::GENERATED_CLASS_NAMESPACE.$className3));
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCompilesWithMultipleRequiredMethodWithArgumentsBind(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $enum = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        'public readonly string $arg1;',
                        'public readonly array $arg2;',
                        sprintf('public readonly %s $arg3;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$enum),
                        <<<'METHOD'
                            public function setArg1(string $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                        METHOD,
                        <<<'METHOD'
                            public function setArg2(array $arg2): void
                            {
                                $this->arg2 = $arg2;
                            }
                        METHOD,
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg3(%s $arg3): void
                            {
                                $this->arg3 = $arg3;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$enum,
                        ),
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
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$enum.php")
                    ->setName($enum)
                    ->setPrefix('enum')
                    ->setBody([
                        'case EnumCaseOne;',
                    ]),
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
                    variableBindings: [
                        '$arg1' => 'env(APP_BOUND_VAR)',
                        '$arg2' => new TaggedIterator('tag'),
                        '$arg3' => constant(self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$enum.'::EnumCaseOne'),
                    ],
                    requiredMethodCalls: [
                        'setArg1',
                        'setArg2',
                        'setArg3',
                    ],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className2,
                    tags: ['tag'],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className3,
                    tags: ['tag'],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        $this->assertInitialized($object, 'arg1');
        $this->assertInitialized($object, 'arg2');
        $this->assertInitialized($object, 'arg3');

        self::assertEquals('bound_variable_value', $object->arg1);
        self::assertSame(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
            ],
            $object->arg2,
        );
        self::assertSame(constant(self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$enum.'::EnumCaseOne'), $object->arg3);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCompilesWithNonSingletonClassAndMultipleRequiredMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        sprintf(
                            <<<'METHOD'
                            public function setArg2(%s $arg2): void
                            {
                                $this->arg2 = $arg2;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
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
                    singleton: false,
                    requiredMethodCalls: ['setArg1', 'setArg2'],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $objectVersion1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $objectVersion2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);

        $this->assertInitialized($objectVersion1, 'arg1');
        $this->assertInitialized($objectVersion1, 'arg2');
        $this->assertInitialized($objectVersion2, 'arg1');
        $this->assertInitialized($objectVersion2, 'arg2');

        self::assertNotSame($objectVersion1, $objectVersion2);
        self::assertSame($objectVersion1->arg1, $class2);
        self::assertSame($objectVersion1->arg2, $class3);
        self::assertSame($objectVersion2->arg1, $class2);
        self::assertSame($objectVersion2->arg2, $class3);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCompilesWithNonSingletonClassAndMultipleRequiredMethodWhichBoundNonSingletonClasses(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        sprintf(
                            <<<'METHOD'
                            public function setArg2(%s $arg2): void
                            {
                                $this->arg2 = $arg2;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
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
                    singleton: false,
                    requiredMethodCalls: ['setArg1', 'setArg2'],
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

        $objectVersion1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $objectVersion2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);

        $this->assertInitialized($objectVersion1, 'arg1');
        $this->assertInitialized($objectVersion1, 'arg2');
        $this->assertInitialized($objectVersion2, 'arg1');
        $this->assertInitialized($objectVersion2, 'arg2');

        self::assertNotSame($objectVersion1, $objectVersion2);
        self::assertInstanceOf($class2::class, $objectVersion1->arg1);
        self::assertInstanceOf($class3::class, $objectVersion1->arg2);
        self::assertInstanceOf($class2::class, $objectVersion2->arg1);
        self::assertInstanceOf($class3::class, $objectVersion2->arg2);
        self::assertNotSame($objectVersion1->arg1, $objectVersion2->arg1);
        self::assertNotSame($objectVersion1->arg2, $objectVersion2->arg2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithTraitWithMultipleRequiredMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $trait = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([sprintf('use %s;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$trait)]),
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
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$trait.php")
                    ->setName($trait)
                    ->setPrefix('trait')
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        sprintf(
                            <<<'METHOD'
                            public function setArg2(%s $arg2): void
                            {
                                $this->arg2 = $arg2;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                    ]),
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
                    requiredMethodCalls: ['setArg1', 'setArg2'],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        $this->assertInitialized($object, 'arg1');
        $this->assertInitialized($object, 'arg2');

        self::assertSame($object->arg1, $container->get(self::GENERATED_CLASS_NAMESPACE.$className2));
        self::assertSame($object->arg2, $container->get(self::GENERATED_CLASS_NAMESPACE.$className3));
    }

    /**
     * @throws ReflectionException
     */
    private function assertInitialized(object $class, string $propertyName): void
    {
        $r = new ReflectionClass($class);

        $property = $r->getProperty($propertyName);
        if (!$property->isInitialized($class)) {
            self::fail(sprintf('Failed asserting property "%s" is initialized.', $propertyName));
        }
    }
}

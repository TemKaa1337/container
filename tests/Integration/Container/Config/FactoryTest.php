<?php

declare(strict_types=1);

namespace Container\Config;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Model\Config\Factory;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

/**
 * @psalm-suppress ArgumentTypeCoercion, MixedPropertyFetch, MixedAssignment
 */
final class FactoryTest extends AbstractContainerTestCase
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
    public function testCompilesWithBoundVariablesForFactoryMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $enum = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        'public readonly string $arg3,',
                        'public readonly int $arg4,',
                        sprintf('public readonly %s $arg5,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$enum),
                        'public readonly int $arg6,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$enum.php")
                    ->setName($enum)
                    ->setPrefix('enum')
                    ->setBody([
                        'case FirstCase;',
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
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'private readonly int $varFour,',
                    ])
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public function create(%s $class1, %s $class2, string $varOne, int $varTwo, \UnitEnum $varThree): %s
                            {
                                return new %s($class1, $class2, $varOne, $varTwo, $varThree, $this->varFour);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            globalBoundVariables: [
                'varOne'   => '1',
                'varTwo'   => '2',
                'varThree' => '3',
                '$varFour' => '4',
            ],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    factory: new Factory(
                        id: self::GENERATED_CLASS_NAMESPACE.$className4,
                        method: 'create',
                        boundedVariables: [
                            '$varOne'   => 'env(APP_BOUND_VAR)',
                            'varTwo'    => 'env(ENV_VAR_2)',
                            '$varThree' => constant(self::GENERATED_CLASS_NAMESPACE.$enum.'::FirstCase'),
                        ],
                    ),
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className4,
                    variableBindings: [
                        '$varFour' => 'env(ENV_INT_VAL)',
                    ],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class1->arg2);
        self::assertEquals('bound_variable_value', $class1->arg3);
        self::assertEquals(10, $class1->arg4);
        self::assertEquals(constant(self::GENERATED_CLASS_NAMESPACE.$enum.'::FirstCase'), $class1->arg5);
        self::assertEquals(3, $class1->arg6);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDynamicFactory(): void
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
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('private readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        sprintf('private readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public function create(): %s
                            {
                                return new %s($this);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    factory: new Factory(
                        id: self::GENERATED_CLASS_NAMESPACE.$className2,
                        method: 'create',
                        boundedVariables: [],
                    ),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf($class2::class, $class1->arg1);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDynamicFactoryWithFactoryMethodParameters(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        $className6 = ClassGenerator::getClassName();
        $factory1 = ClassGenerator::getClassName();
        $factory2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factory1,
                            'create',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
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
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$factory1.php")
                    ->setName($factory1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('private readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public function create(%s $class2): %s
                            {
                                return new %s($this->arg1, $class2);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className5),
                        sprintf('public readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className6),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className6.php")
                    ->setName($className6),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$factory2.php")
                    ->setName($factory2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('private readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className5),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public function create(%s $class2): %s
                            {
                                return new %s($this->arg1, $class2);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className6,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className5.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className6.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className4,
                    factory: new Factory(
                        id: self::GENERATED_CLASS_NAMESPACE.$factory2,
                        method: 'create',
                        boundedVariables: [],
                    ),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class1->arg2);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className5, $class2->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className6, $class2->arg2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithNonExistingEnvVariableWithDefaultValue(): void
    {
        $className1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_NAMESPACE.$className1,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorVisibility('private')
                    ->setConstructorArguments([
                        'public readonly ?string $argument,',
                    ])
                    ->setBody([
                        <<<'METHOD'
                            public static function create(string $arg1 = null): self
                            {
                                return new self($arg1);
                            }
                        METHOD,
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    factory: new Factory(
                        id: self::GENERATED_CLASS_NAMESPACE.$className1,
                        method: 'create',
                        boundedVariables: ['arg1' => 'env(SOME_NON_EXISTING_ENV_VARIABLE)'],
                    ),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();
        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class);
        self::assertSame(null, $class->argument);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithStaticFactory(): void
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
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('private readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        sprintf('private readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public static function create(): %s
                            {
                                return new %s(new self(new %s(), new %s()));
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    factory: new Factory(
                        id: self::GENERATED_CLASS_NAMESPACE.$className2,
                        method: 'create',
                        boundedVariables: [],
                    ),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf($class2::class, $class1->arg1);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithStaticFactoryWithFactoryMethodParameters(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        $className6 = ClassGenerator::getClassName();
        $factory1 = ClassGenerator::getClassName();
        $factory2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factory1,
                            'create',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
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
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$factory1.php")
                    ->setName($factory1)
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s $class1, %s $class2): %s
                            {
                                return new %s($class1, $class2);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg1,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className5),
                        sprintf('public readonly %s $arg2,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className6),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className6.php")
                    ->setName($className6),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$factory2.php")
                    ->setName($factory2)
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s $class1, %s $class2): %s
                            {
                                return new %s($class1, $class2);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className5,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className6,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className5.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className6.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className4,
                    factory: new Factory(
                        id: self::GENERATED_CLASS_NAMESPACE.$factory2,
                        method: 'create',
                        boundedVariables: [],
                    ),
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class1->arg2);

        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className5, $class2->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className6, $class2->arg2);
    }
}

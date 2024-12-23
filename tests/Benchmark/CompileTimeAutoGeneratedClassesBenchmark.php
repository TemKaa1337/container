<?php

declare(strict_types=1);

namespace Tests\Benchmark;

use DirectoryIterator;
use PhpBench\Attributes\AfterMethods;
use PhpBench\Attributes\Assert;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Model\Config;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use function putenv;
use function realpath;
use function sprintf;
use function unlink;

/**
 * @psalm-suppress MissingConstructor
 */
final class CompileTimeAutoGeneratedClassesBenchmark
{
    private const string ATTRIBUTE_AUTOWIRE_SIGNATURE = '#[\Temkaa\Container\Attribute\Autowire(load: %s, singleton: %s)]';
    private const string ATTRIBUTE_DECORATES_SIGNATURE = '#[\Temkaa\Container\Attribute\Decorates(id: %s, priority: %s)]';
    private const string ATTRIBUTE_FACTORY_SIGNATURE = '#[\Temkaa\Container\Attribute\Factory(id: \'%s\', method: \'%s\')]';
    private const string ATTRIBUTE_INSTANCE_OF_ITERATOR_SIGNATURE = '#[\Temkaa\Container\Attribute\Bind\InstanceOfIterator(id: %s)]';
    private const string ATTRIBUTE_PARAMETER_SIGNATURE = '#[\Temkaa\Container\Attribute\Bind\Parameter(expression: \'%s\')]';
    private const string ATTRIBUTE_REQUIRED_SIGNATURE = '#[\Temkaa\Container\Attribute\Bind\Required()]';
    private const string ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE = '#[\Temkaa\Container\Attribute\Bind\TaggedIterator(tag: \'%s\')]';
    private const string ATTRIBUTE_TAG_SIGNATURE = '#[\Temkaa\Container\Attribute\Tag(name: \'%s\')]';
    private const string GENERATED_CLASS_ABSOLUTE_NAMESPACE = '\Tests\Fixture\Stub\Class\\';
    private const string GENERATED_CLASS_NAMESPACE = 'Tests\Fixture\Stub\Class\\';
    private const string GENERATED_CLASS_STUB_PATH = '/../Fixture/Stub/Class/';

    private Config $config;

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    #[AfterMethods('tearDown')]
    #[Assert(expression: 'mode(variant.time.avg) < 4.5 milliseconds')] // latest best local run - 2.6ms (4.3ms in GHA)
    #[BeforeMethods('setUp')]
    #[Iterations(20)]
    #[Revs(1000)]
    #[Warmup(revs: 1)]
    public function benchCompilesInSuitableTime(): void
    {
        (new ContainerBuilder())->add($this->config)->build();
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp(): void
    {
        $required1 = ClassGenerator::getClassName();
        $required2 = ClassGenerator::getClassName();
        $required3 = ClassGenerator::getClassName();
        $required4 = ClassGenerator::getClassName();

        $factoryDecorator1 = ClassGenerator::getClassName();
        $factoryDecorator2 = ClassGenerator::getClassName();
        $factoryDecorator3 = ClassGenerator::getClassName();
        $factoryClass = ClassGenerator::getClassName();

        $factoryDecoratorInterface = ClassGenerator::getClassName();

        $decorator1 = ClassGenerator::getClassName();
        $decorator2 = ClassGenerator::getClassName();
        $decorator3 = ClassGenerator::getClassName();
        $decoratedClass = ClassGenerator::getClassName();

        $decoratedInterface = ClassGenerator::getClassName();

        $interfaceName1 = ClassGenerator::getClassName();
        $interfaceName2 = ClassGenerator::getClassName();
        $interfaceName3 = ClassGenerator::getClassName();
        $interfaceName4 = ClassGenerator::getClassName();

        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        $className6 = ClassGenerator::getClassName();

        $className7 = ClassGenerator::getClassName();
        $className8 = ClassGenerator::getClassName();
        $className9 = ClassGenerator::getClassName();
        $className10 = ClassGenerator::getClassName();

        $className11 = ClassGenerator::getClassName();
        $className12 = ClassGenerator::getClassName();
        $className13 = ClassGenerator::getClassName();
        $className14 = ClassGenerator::getClassName();

        $className15 = ClassGenerator::getClassName();
        $className16 = ClassGenerator::getClassName();
        $className17 = ClassGenerator::getClassName();
        $className18 = ClassGenerator::getClassName();
        $className19 = ClassGenerator::getClassName();

        $collectorClassName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$required1.php")
                    ->setName($required1)
                    ->setBody([
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'BODY'
                                public function setDecorator(%s $decorator): void
                                {
                                }
                            BODY,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$required2.php")
                    ->setName($required2)
                    ->setBody([
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'BODY'
                                public function setDecorator(%s $decorator): void
                                {
                                }
                            BODY,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$decoratedInterface,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$required3.php")
                    ->setName($required3)
                    ->setBody([
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        <<<'BODY'
                            public function required1(): void
                            {
                            }
                        BODY,
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        <<<'BODY'
                            public function required2(): void
                            {
                            }
                        BODY,
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        <<<'BODY'
                            public function required3(): void
                            {
                            }
                        BODY,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$required4.php")
                    ->setName($required4)
                    ->setBody([
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'BODY'
                                public function setOne(%s $class): void
                                {
                                }
                            BODY,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className12,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'BODY'
                                public function setTwo(%s $class): void
                                {
                                }
                            BODY,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className13,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'BODY'
                                public function setThree(%s $class): void
                                {
                                }
                            BODY,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className14,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(
                        realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$factoryDecoratorInterface.php",
                    )
                    ->setName($factoryDecoratorInterface)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$factoryClass.php")
                    ->setName($factoryClass)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface])
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryClass,
                            'create',
                        ),
                    ])
                    ->setBody([
                        <<<BODY
                        public static function create(): self
                        {
                            return new self();
                        }
                        BODY,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$factoryDecorator1.php")
                    ->setName($factoryDecorator1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface])
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface.'::class',
                            0,
                        ),
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecorator1,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'private readonly %s $decorated,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface,
                        ),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'BODY'
                                public static function create(%s $decorated): self
                                {
                                    return new self($decorated);
                                }
                            BODY,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$factoryDecorator2.php")
                    ->setName($factoryDecorator2)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface])
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface.'::class',
                            1,
                        ),
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecorator2,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'private readonly %s $decorated,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface,
                        ),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'BODY'
                                public static function create(%s $decorated): self
                                {
                                    return new self($decorated);
                                }
                            BODY,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$factoryDecorator3.php")
                    ->setName($factoryDecorator3)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface])
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface.'::class',
                            2,
                        ),
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecorator3,
                            'create',
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'private readonly %s $decorated,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface,
                        ),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'BODY'
                                public static function create(%s $decorated): self
                                {
                                    return new self($decorated);
                                }
                            BODY,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$factoryDecoratorInterface,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className15.php")
                    ->setName($className15)
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className18])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $dependency1 = "dep",',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className16.php")
                    ->setName($className16)
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className18])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $dependency1 = "dep",',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className17.php")
                    ->setName($className17)
                    ->setExtends([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className18])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $dependency1 = "dep",',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className18.php")
                    ->setName($className18)
                    ->setPrefix('abstract'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className19.php")
                    ->setName($className19)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_INSTANCE_OF_ITERATOR_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className18.'::class',
                        ),
                        'private readonly array $abstracts,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$decoratedClass.php")
                    ->setName($decoratedClass)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$decoratedInterface])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $dependency1 = "dep",',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$decorator1.php")
                    ->setName($decorator1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$decoratedInterface])
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$decoratedInterface.'::class',
                            0,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$decoratedInterface,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$decorator2.php")
                    ->setName($decorator2)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$decoratedInterface])
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$decoratedInterface.'::class',
                            1,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$decoratedInterface,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$decorator3.php")
                    ->setName($decorator3)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$decoratedInterface])
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$decoratedInterface.'::class',
                            2,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$decoratedInterface,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className11.php")
                    ->setName($className11)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName4])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $dependency1 = "dep",',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className12.php")
                    ->setName($className12)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName4])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $dependency1 = "dep",',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className13.php")
                    ->setName($className13)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName4])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $dependency1 = "dep",',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName4.php")
                    ->setName($interfaceName4)
                    ->setPrefix('interface')
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'some_tag'),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className14.php")
                    ->setName($className14)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE, 'some_tag'),
                        'public readonly array $tagged,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className7.php")
                    ->setName($className7)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName3])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $dependency1 = "dep",',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className8.php")
                    ->setName($className8)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName3])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className9.php")
                    ->setName($className9)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName3])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className10.php")
                    ->setName($className10)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_INSTANCE_OF_ITERATOR_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName3.'::class',
                        ),
                        'public readonly array $instances,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName3.php")
                    ->setName($interfaceName3)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName1.php")
                    ->setName($interfaceName1)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1.'::class',
                            0,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_PARAMETER_SIGNATURE,
                            'env(ENV_VAR_1)',
                        ),
                        'public readonly string $arg,',
                        sprintf(
                            'public readonly %s $inner,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                        ),
                        sprintf(self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE, 'Interface2'),
                        'public readonly iterable $dependency,',
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName2.php")
                    ->setName($interfaceName2)
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'Interface2')])
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$decoratedInterface.php")
                    ->setName($decoratedInterface)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2])
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2])
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly int $varOne,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly string $varTwo,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly float $varThree,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_BOOL_VAL)'),
                        'public readonly bool $varFour,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_FLOAT_VAR)'),
                        'public readonly mixed $varFive,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_BOOL_VAL)'),
                        'public readonly mixed $varSix,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_INT_VAL)'),
                        'public readonly mixed $varSeven,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_STRING_VAL)'),
                        'public readonly mixed $varEight,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_VARIABLE_REFERENCE)'),
                        'public readonly mixed $varReference,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className6.php")
                    ->setName($className6),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$collectorClassName.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$decoratedInterface.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className5.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className6.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className7.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className8.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className9.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className10.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className11.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className12.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className13.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className14.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$decorator1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$decorator2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$decorator3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$decoratedClass.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$factoryDecorator1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$factoryDecorator2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$factoryDecorator3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$factoryClass.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$factoryDecoratorInterface.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$required1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$required2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$required3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$required4.php",
        ];

        $this->config = $this->generateConfig(
            includedPaths: $files,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName1 => self::GENERATED_CLASS_NAMESPACE.$className1,
            ],
        );

        $envVariables = [
            'APP_BOUND_VAR'           => 'bound_variable_value',
            'ENV_CASTABLE_STRING_VAR' => '10.1',
            'ENV_FLOAT_VAR'           => '10.1',
            'ENV_BOOL_VAL'            => 'false',
            'ENV_INT_VAL'             => '3',
            'ENV_STRING_VAL'          => 'string',
            'ENV_STRING_VAR'          => 'string',
            'ENV_VAR_1'               => 'test_one',
            'ENV_VARIABLE_REFERENCE'  => 'env(ENV_STRING_VAR)_additional_string',
        ];

        foreach ($envVariables as $name => $value) {
            putenv("$name=$value");
        }
    }

    public function tearDown(): void
    {
        $path = realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH);

        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot() || $file->isDir() || $file->getFilename() === '.gitkeep') {
                continue;
            }

            unlink($file->getRealPath());
        }
    }

    private function generateConfig(
        array $includedPaths = [],
        array $interfaceBindings = [],
    ): Config {
        $builder = new ConfigBuilder();

        /** @psalm-suppress MixedAssignment, MixedArgument */
        foreach ($includedPaths as $path) {
            $builder->include($path);
        }

        /** @psalm-suppress MixedAssignment, MixedArgument, MixedArgumentTypeCoercion */
        foreach ($interfaceBindings as $interface => $class) {
            $builder->bindInterface($interface, $class);
        }

        return $builder->build();
    }
}

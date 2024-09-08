<?php

declare(strict_types=1);

namespace Tests\Integration\Container;

use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Exception\CircularReferenceException;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\EntryNotFoundException as ConfigEntryNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\InvalidPathException;
use Temkaa\SimpleContainer\Exception\EntryNotFoundException;
use Temkaa\SimpleContainer\Exception\NonAutowirableClassException;
use Temkaa\SimpleContainer\Exception\UninstantiableEntryException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Repository\DefinitionRepository;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 *
 * @psalm-suppress ArgumentTypeCoercion, InternalClass, InternalMethod, MixedAssignment, MixedArrayAccess
 * @psalm-suppress MixedPropertyFetch
 */
final class GeneralTest extends AbstractContainerTestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompileClassWithoutDependencies(): void
    {
        $className = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className),
        );
        self::assertIsObject($object);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $object);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDefaultAutowireTagValues(): void
    {
        $className1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([self::ATTRIBUTE_AUTOWIRE_DEFAULT_SIGNATURE]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className1));
        self::assertSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithExplicitDependencySetting(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
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
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                        sprintf(
                            'public readonly %s $dependency2,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        sprintf(
                            'public readonly %s $dependency3,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
        ];

        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        self::assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className1));
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
        );

        self::assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className2));
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
        );

        self::assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className3));
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className3,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
        );

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);
        self::assertIsObject($object->dependency1);
        self::assertIsObject($object->dependency2);
        self::assertIsObject($object->dependency3);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $object->dependency1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $object->dependency2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $object->dependency3);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithIncludedClassesFromFolder(): void
    {
        self::clearClassFixtures();

        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
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
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH];

        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        self::assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className1));
        self::assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className2));
        self::assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className3));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInterfaceBindingByClass(): void
    {
        $className = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->generate();

        $classPaths = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classPaths,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName => self::GENERATED_CLASS_NAMESPACE.$className,
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interfaceName, $class);
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$interfaceName));
        self::assertSame($class, $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInterfaceBindingByClassInjectedInAnotherClass(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        (new ClassGenerator())
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
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->generate();

        $classPaths = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $classPaths,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName => self::GENERATED_CLASS_NAMESPACE.$className1,
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interfaceName, $class);
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$interfaceName));
        self::assertSame($class, $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName));

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interfaceName, $class->arg);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class->arg);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInterfaceTagInheritance(): void
    {
        $className = ClassGenerator::getClassName();
        $interfaceName1 = ClassGenerator::getClassName();
        $interfaceFullNamespace1 = self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1;
        $interfaceName2 = ClassGenerator::getClassName();
        $interfaceFullNamespace2 = self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2;
        $interfaceName3 = ClassGenerator::getClassName();
        $interfaceFullNamespace3 = self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName3;
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName1.php")
                    ->setName($interfaceName1)
                    ->setPrefix('interface')
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'interface1')]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName2.php")
                    ->setName($interfaceName2)
                    ->setPrefix('interface')
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'interface2')]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName3.php")
                    ->setName($interfaceName3)
                    ->setPrefix('interface')
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'interface3')])
                    ->setExtends([$interfaceFullNamespace1, $interfaceFullNamespace2]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'class1')])
                    ->setInterfaceImplementations([$interfaceFullNamespace3]),
            )
            ->generate();

        $classes = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName3.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName2.'.php',
        ];

        $config = $this->generateConfig(includedPaths: $classes);

        $container = (new ContainerBuilder())->add($config)->build();

        $reflection = new ReflectionClass($container);

        /** @var DefinitionRepository $definitionRepository */
        $definitionRepository = $reflection->getProperty('definitionRepository')->getValue($container);

        $reflection = new ReflectionClass($definitionRepository);
        /** @var Bag $definitions */
        $definitions = $reflection->getProperty('definitions')->getValue($definitionRepository);

        /** @var ClassDefinition $classDefinition */
        $classDefinition = $definitions->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertEqualsCanonicalizing(
            [
                'class1',
                'interface3',
                'interface1',
                'interface2',
            ],
            $classDefinition->getTags(),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInterfaceTypeHintedInClassWithoutExplicitBinding(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        (new ClassGenerator())
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
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->generate();

        $classPaths = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
        ];

        $config = $this->generateConfig(includedPaths: $classPaths);

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interfaceName, $class);
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$interfaceName));
        self::assertSame($class, $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName));

        $classWithDependency = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $classWithDependency);
        self::assertSame($class, $classWithDependency->arg);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithInterfaceWithoutExplicitBinding(): void
    {
        $className = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->generate();

        $classPaths = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
        ];

        $config = $this->generateConfig(includedPaths: $classPaths);

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interfaceName, $class);
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$interfaceName));
        self::assertSame($class, $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithNonSingletonDependency(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')]),
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
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);

        self::assertNotSame(
            $class1,
            $class2->arg,
        );
        self::assertNotSame(
            $class2->arg,
            $class3->arg,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithNonSingletonFromAttribute(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        self::assertNotSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithNonSingletonFromConfig(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    singleton: false,
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        self::assertNotSame(
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className),
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getDataForCompilesWithUninstantiableEntryTest')]
    public function testCompilesWithUninstantiableEntry(
        string $className,
        string $classNamePrefix,
        array $attributes,
        string $constructorVisibility,
    ): void {
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setAttributes($attributes)
                    ->setPrefix($classNamePrefix)
                    ->setConstructorVisibility($constructorVisibility)
                    ->setConstructorArguments([
                        'public readonly array $dependency1,',
                        'public readonly iterable $dependency2,',
                    ]),
            )
            ->generate();

        $classPath = __DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php';
        $config = $this->generateConfig(includedPaths: [$classPath]);

        $container = (new ContainerBuilder())->add($config)->build();

        self::assertFalse($container->has($className));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithoutSettingAllDependenciesClassWithDependencies(): void
    {
        $collectorName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
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
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorName.php")
                    ->setName($collectorName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                        sprintf(
                            'public readonly %s $dependency2,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        sprintf(
                            'public readonly %s $dependency3,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                    ]),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorName.'.php'];

        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        self::assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className1));
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
        );

        self::assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className2));
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className2,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
        );

        self::assertIsObject($container->get(self::GENERATED_CLASS_NAMESPACE.$className3));
        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className3,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
        );

        $collector = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorName);
        self::assertIsObject($collector->dependency1);
        self::assertIsObject($collector->dependency2);
        self::assertIsObject($collector->dependency3);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $collector->dependency1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $collector->dependency2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $collector->dependency3);

        self::assertSame(
            $collector->dependency1,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
        );
        self::assertSame(
            $collector->dependency2,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
        );
        self::assertSame(
            $collector->dependency3,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompliesWithNullableVariable(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments(['public readonly ?string $arg']),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];

        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $classFullNamespace = self::GENERATED_CLASS_NAMESPACE.$className;

        $class = $container->get($classFullNamespace);
        self::assertInstanceOf($classFullNamespace, $class);
        self::assertNull($class->arg);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getDataForDoesNotCompileDueToInternalClassDependencyTest')]
    public function testDoesNotCompileDueToInternalClassDependency(
        string $className,
        string $constructorArgument,
        string $argumentClassName,
    ): void {
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([$constructorArgument]),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(UninstantiableEntryException::class);
        $this->expectExceptionMessage(sprintf('Cannot resolve internal entry "%s".', $argumentClassName));

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToMissingArgumentClass(): void
    {
        $className1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'private readonly %s $arg,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.'NonExistentClass',
                        ),
                    ]),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php'];

        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf('Class "%s" is not found.', self::GENERATED_CLASS_NAMESPACE.'NonExistentClass'),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToMissingInterfaceImplementation(): void
    {
        $className = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf('public readonly %s $arg,', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->generate();

        $classPaths = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
        ];

        $config = $this->generateConfig(includedPaths: $classPaths);

        $this->expectException(ConfigEntryNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not find interface implementation for "%s".',
                self::GENERATED_CLASS_NAMESPACE.$interfaceName,
            ),
        );
        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToMultipleInterfaceImplementationsAndWithoutExplicitBinding(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        (new ClassGenerator())
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
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->generate();

        $classPaths = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
        ];

        $config = $this->generateConfig(includedPaths: $classPaths);

        $container = (new ContainerBuilder())->add($config)->build();
        self::assertFalse($container->has(self::GENERATED_CLASS_NAMESPACE.$interfaceName));
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className1));
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$className2));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getDataForDoesNotCompileDueToNotDeterminedArgumentTypeTest')]
    public function testDoesNotCompileDueToNotDeterminedArgumentType(
        string $className,
        string $constructorArgument,
        string $exceptionMessage,
    ): void {
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([$constructorArgument]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
        );

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithBuiltInTypedArgument(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments(['public readonly int $age,']),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "%s::%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
                'age',
                'int',
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithCircularDependencies(): void
    {
        $circularClassName1 = ClassGenerator::getClassName();
        $circularClassName2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$circularClassName1.php")
                    ->setName($circularClassName1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$circularClassName2,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$circularClassName2.php")
                    ->setName($circularClassName2)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$circularClassName1,
                        ),
                    ]),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$circularClassName1.php"];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(CircularReferenceException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate class "%s" as it has circular references "%s".',
                $circularClassName1,
                "$circularClassName1 -> $circularClassName2",
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithExcludedDependency(): void
    {
        $collectorName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
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
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorName.php")
                    ->setName($collectorName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                        sprintf(
                            'public readonly %s $dependency2,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        sprintf(
                            'public readonly %s $dependency3,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                    ]),
            )
            ->generate();

        $includeFiles = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorName.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
        ];
        $excludeFiles = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
        ];

        $config = $this->generateConfig(
            includedPaths: $includeFiles,
            excludedPaths: $excludeFiles,
        );

        $this->expectException(NonAutowirableClassException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot autowire class "%s" as it is in "exclude" config parameter.',
                self::GENERATED_CLASS_NAMESPACE.$className3,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithInterfaceDependencyWithoutExplicitBinding(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        (new ClassGenerator())
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
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->generate();

        $classPaths = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$className3.'.php',
            __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
        ];

        $config = $this->generateConfig(includedPaths: $classPaths);

        $this->expectException(ConfigEntryNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not find interface implementation for "%s".',
                self::GENERATED_CLASS_NAMESPACE.$interfaceName,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithNonAutowirableAttributeClass(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $invalidClassName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$invalidClassName,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$invalidClassName.php")
                    ->setName($invalidClassName)
                    ->setHasConstructor(true)
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'false', 'true')]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php',
            ],
        );

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NonAutowirableClassException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Class "%s" has NonAutowirable attribute and cannot be autowired.',
                self::GENERATED_CLASS_NAMESPACE.$invalidClassName,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    public function testDoesNotCompileWithNonExistentClassExcludedPaths(): void
    {
        $classPath = __DIR__.self::GENERATED_CLASS_STUB_PATH.'NonExistentClass.php';
        $config = $this->generateConfig(excludedPaths: [$classPath]);

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('The specified path "'.$classPath.'" does not exist.');

        (new ContainerBuilder())->add($config);
    }

    public function testDoesNotCompileWithNonExistentClassInIncludedPaths(): void
    {
        $classPath = __DIR__.self::GENERATED_CLASS_STUB_PATH.'NonExistentClass.php';
        $config = $this->generateConfig(includedPaths: [$classPath]);

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('The specified path "'.$classPath.'" does not exist.');

        (new ContainerBuilder())->add($config);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithNonTypedArgument(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments(['$arg']),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(UninstantiableEntryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry with non-typed parameters "%s" -> "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
                'arg',
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithTypeHintedEnum(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $enumClassName = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$enumClassName,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$enumClassName.php")
                    ->setName($enumClassName)
                    ->setPrefix('enum'),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$collectorClassName.php"];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(UninstantiableEntryException::class);
        $this->expectExceptionMessage(
            sprintf('Cannot instantiate entry with id "%s".', self::GENERATED_CLASS_NAMESPACE.$enumClassName),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getDataForDoesNotCompileWithUninstantiableEntryTest')]
    public function testDoesNotCompileWithUninstantiableEntry(
        string $collectorClassName,
        string $invalidClassName,
        string $classNamePrefix,
        string $constructorVisibility,
        array $collectorConstructorArguments,
    ): void {
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments($collectorConstructorArguments),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$invalidClassName.php")
                    ->setName($invalidClassName)
                    ->setHasConstructor(true)
                    ->setConstructorVisibility($constructorVisibility)
                    ->setPrefix($classNamePrefix)
                    ->setConstructorArguments($collectorConstructorArguments),
            )
            ->generate();

        $classPath = __DIR__.self::GENERATED_CLASS_STUB_PATH.$collectorClassName.'.php';
        $config = $this->generateConfig(includedPaths: [$classPath]);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(UninstantiableEntryException::class);
        $this->expectExceptionMessage(
            sprintf('Cannot instantiate entry with id "%s".', self::GENERATED_CLASS_NAMESPACE.$invalidClassName),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithoutInterfaceDefaultArgumentInFactoryMethodWhichIsNotLoaded(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setBody([
                        sprintf(
                            <<<'METHOD'
                            public static function create(%s $arg): self
                            {
                                return new self($arg);
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_FACTORY_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                            'create',
                        ),
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
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            ],
        );

        $this->expectException(ConfigEntryNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf('Could not find interface implementation for "%s".', self::GENERATED_CLASS_NAMESPACE.$interface),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithoutInterfaceDefaultArgumentInRequiredMethodWhichIsNotLoaded(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setBody([
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function create(%s $arg): void
                            {
                                
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
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ]),
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
            ],
        );

        $this->expectException(ConfigEntryNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf('Could not find interface implementation for "%s".', self::GENERATED_CLASS_NAMESPACE.$interface),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testWithoutCompiling(): void
    {
        $container = (new ContainerBuilder())->build();

        $this->expectException(EntryNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Could not find entry "%s".', 'asd'));

        $container->get('asd');
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration\Config;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\CannotBindInterfaceException;
use Temkaa\SimpleContainer\Exception\Config\EnvVariableCircularException;
use Temkaa\SimpleContainer\Exception\Config\EnvVariableNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\InvalidPathException;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Model\Definition\InterfaceDefinition;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\AbstractTestCase;

/**
 * @psalm-suppress ArgumentTypeCoercion, PossiblyInvalidArrayOffset, InternalClass, InternalMethod, MixedArrayAccess
 * @psalm-suppress MixedAssignment, MixedArgument
 */
final class BuilderTest extends AbstractTestCase
{
    /**
     * @psalm-suppress InvalidClassConstantType
     */
    protected const string GENERATED_CLASS_STUB_PATH = '/../../Fixture/Stub/Class/';

    public function testConfigDoesNotInitDueToInvalidServicePath(): void
    {
        $config = $this->generateConfig(includedPaths: ['path']);

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('The specified path "path" does not exist.');

        (new ContainerBuilder())->add($config);
    }

    public function testConfigDoesNotLoadDueToBoundInterfaceImplementationDoNotImplementInterface(): void
    {
        $interfaceName1 = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName1.php")
                    ->setName($interfaceName1)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName1.php"];
        $config = $this->generateConfig(
            includedPaths: $files,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName1 => self::GENERATED_CLASS_NAMESPACE.$className1,
            ],
        );

        $this->expectException(CannotBindInterfaceException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot bind class "%s" to interface "%s" as it doesn\'t implement it.',
                self::GENERATED_CLASS_NAMESPACE.$className1,
                self::GENERATED_CLASS_NAMESPACE.$interfaceName1,
            ),
        );
        (new ContainerBuilder())->add($config);
    }

    public function testConfigDoesNotLoadDueToBoundInterfaceImplementationIsNotAnInterface(): void
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
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$className1 => self::GENERATED_CLASS_NAMESPACE.$className2,
            ],
        );

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf('Interface "%s" is not found.', self::GENERATED_CLASS_NAMESPACE.$className1),
        );

        (new ContainerBuilder())->add($config);
    }

    public function testConfigDoesNotLoadDueToMissingBoundClass(): void
    {
        $className1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php"];
        /** @psalm-suppress UndefinedClass */
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: 'non_existing_class',
                ),
            ],
        );

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage('Class "non_existing_class" is not found.');

        (new ContainerBuilder())->add($config);
    }

    public function testConfigDoesNotLoadDueToMissingInterface(): void
    {
        $config = $this->generateConfig(interfaceBindings: ['non_existent_interface' => 'class']);

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage('Interface "non_existent_interface" is not found.');

        (new ContainerBuilder())->add($config);
    }

    public function testConfigDoesNotLoadDueToMissingInterfaceImplementation(): void
    {
        $interfaceName1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName1.php")
                    ->setName($interfaceName1)
                    ->setPrefix('interface'),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName1.php"];
        $config = $this->generateConfig(
            includedPaths: $files,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName1 => 'class',
            ],
        );

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage('Class "class" is not found.');

        (new ContainerBuilder())->add($config);
    }

    public function testConfigDoesNotLoadsDueToCircularEnvVariableClassBinding(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $circular,',
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: ['$circular' => 'env(CIRCULAR_ENV_VARIABLE_1)'],
                ),
            ],
        );

        $this->expectException(EnvVariableCircularException::class);
        $this->expectExceptionMessage(
            'Cannot resolve env variable "env(CIRCULAR_ENV_VARIABLE_2)" as '
            .'it has circular references "CIRCULAR_ENV_VARIABLE_1 -> CIRCULAR_ENV_VARIABLE_2".',
        );

        (new ContainerBuilder())->add($config);
    }

    public function testConfigDoesNotLoadsDueToCircularEnvVariableGlobalBinding(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly string $circular,',
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
            globalBoundVariables: ['$circular' => 'env(CIRCULAR_ENV_VARIABLE_1)'],
        );

        $this->expectException(EnvVariableCircularException::class);
        $this->expectExceptionMessage(
            'Cannot resolve env variable "env(CIRCULAR_ENV_VARIABLE_2)" as '
            .'it has circular references "CIRCULAR_ENV_VARIABLE_1 -> CIRCULAR_ENV_VARIABLE_2".',
        );

        (new ContainerBuilder())->add($config);
    }

    public function testConfigHasEnvBoundVariables(): void
    {
        $className = ClassGenerator::getClassName();
        $classFullNamespace = self::GENERATED_CLASS_NAMESPACE.$className;
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className),
            )
            ->generate();

        $config = $this->generateConfig(
            classBindings: [
                $this->generateClassConfig(
                    className: $classFullNamespace,
                    variableBindings: ['string' => 'env(APP_BOUND_VAR)'],
                ),
            ],
        );

        self::assertEquals(
            ['string' => 'env(APP_BOUND_VAR)'],
            $config->getBoundedClasses()[$classFullNamespace]->getBoundedVariables(),
        );
    }

    public function testConfigHasNonExistentEnvBoundVariables(): void
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
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: ['string' => 'env(APP_DEBUG)'],
                ),
            ],
        );

        $this->expectException(EnvVariableNotFoundException::class);
        $this->expectExceptionMessage('Variable "APP_DEBUG" is not found in env variables.');

        (new ContainerBuilder())->add($config);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testConfigLoadsWithCorrectDecoratorPriorities(): void
    {
        // currently is not used, but might be needed in future
        $interfaceName = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $collectorClassName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                        sprintf(
                            'public readonly %s $dependency2,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className4,
                        ),
                        sprintf(
                            'public readonly %s $dependency3,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                        sprintf(
                            'public readonly %s $dependency4,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        sprintf(
                            'public readonly %s $dependency5,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
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
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            3,
                            'dependency',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dep',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            2,
                            'dependency',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $class',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName.'::class',
                            1,
                            'dependency',
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $property',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$collectorClassName.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName => self::GENERATED_CLASS_NAMESPACE.$className1,
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $reflection = new ReflectionClass($container);
        $definitionRepository = $reflection->getProperty('definitionRepository')->getValue($container);
        $reflection = new ReflectionClass($definitionRepository);
        $definitions = $reflection->getProperty('definitions')->getValue($definitionRepository);

        /** @var InterfaceDefinition $interfaceDefinition */
        $interfaceDefinition = $definitions[self::GENERATED_CLASS_NAMESPACE.$interfaceName];
        /** @var ClassDefinition $rootDecoratedClass */
        $rootDecoratedClass = $definitions[self::GENERATED_CLASS_NAMESPACE.$className1];
        /** @var ClassDefinition $firstLevelDecoratorClass */
        $firstLevelDecoratorClass = $definitions[self::GENERATED_CLASS_NAMESPACE.$className2];
        /** @var ClassDefinition $secondLevelDecoratorClass */
        $secondLevelDecoratorClass = $definitions[self::GENERATED_CLASS_NAMESPACE.$className3];
        /** @var ClassDefinition $thirdLevelDecoratorClass */
        $thirdLevelDecoratorClass = $definitions[self::GENERATED_CLASS_NAMESPACE.$className4];

        self::assertEquals(self::GENERATED_CLASS_NAMESPACE.$className2, $interfaceDefinition->getDecoratedBy());
        self::assertNull($rootDecoratedClass->getDecorates());
        self::assertNull($rootDecoratedClass->getDecoratedBy());

        $decorates = $firstLevelDecoratorClass->getDecorates();
        self::assertNotNull($decorates);
        self::assertEquals(self::GENERATED_CLASS_NAMESPACE.$interfaceName, $decorates->getId());
        self::assertEquals(3, $decorates->getPriority());
        self::assertEquals(self::GENERATED_CLASS_NAMESPACE.$className3, $firstLevelDecoratorClass->getDecoratedBy());

        $decorates = $secondLevelDecoratorClass->getDecorates();
        self::assertNotNull($decorates);
        self::assertEquals(self::GENERATED_CLASS_NAMESPACE.$className2, $decorates->getId());
        self::assertEquals(2, $decorates->getPriority());
        self::assertEquals(self::GENERATED_CLASS_NAMESPACE.$className4, $secondLevelDecoratorClass->getDecoratedBy());

        $decorates = $thirdLevelDecoratorClass->getDecorates();
        self::assertNotNull($decorates);
        self::assertEquals(self::GENERATED_CLASS_NAMESPACE.$className3, $decorates->getId());
        self::assertEquals(1, $decorates->getPriority());
        self::assertNull($thirdLevelDecoratorClass->getDecoratedBy());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testConfigLoadsWithNestedPathsAndNonPhpFiles(): void
    {
        self::clearClassFixtures();

        $className1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1),
            )
            ->generate();

        $generatedClassStubPath = explode('/', __DIR__.self::GENERATED_CLASS_STUB_PATH);
        array_pop($generatedClassStubPath);
        array_pop($generatedClassStubPath);

        $files = [implode('/', $generatedClassStubPath)];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        self::assertInstanceOf(
            self::GENERATED_CLASS_NAMESPACE.$className1,
            $container->get(self::GENERATED_CLASS_NAMESPACE.$className1),
        );
    }
}

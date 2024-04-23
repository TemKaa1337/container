<?php

declare(strict_types=1);

namespace Tests\Unit;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Temkaa\SimpleContainer\Container\Builder;
use Temkaa\SimpleContainer\Exception\CircularReferenceException;
use Temkaa\SimpleContainer\Exception\Config\EnvVariableCircularException;
use Temkaa\SimpleContainer\Exception\Config\InvalidPathException;
use Temkaa\SimpleContainer\Exception\EntryNotFoundException;
use Temkaa\SimpleContainer\Exception\NonAutowirableClassException;
use Temkaa\SimpleContainer\Exception\UninstantiableEntryException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Model\Definition;
use Temkaa\SimpleContainer\Repository\DefinitionRepository;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Throwable;

/**
 * @psalm-suppress ArgumentTypeCoercion
 *
 * @noinspection   PhpArgumentWithoutNamedIdentifierInspection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class ContainerTest extends AbstractContainerTestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testCompileClassWithBuiltInTypedArgument(): void
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

        $files = [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className.php"];
        $configFile = $this->generateConfig(services: ['include' => $files]);

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

        (new Builder())->add($configFile)->compile();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testCompileClassWithCircularDependencies(): void
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

        $files = [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$circularClassName1.php"];
        $configFile = $this->generateConfig(services: ['include' => $files]);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(CircularReferenceException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate class "%s" as it has circular references "%s".',
                $circularClassName1,
                "$circularClassName1 -> $circularClassName2",
            ),
        );

        (new Builder())->add($configFile)->compile();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testCompileClassWithNonTypedArgument(): void
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

        $files = [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className.php"];
        $configFile = $this->generateConfig(services: ['include' => $files]);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(UninstantiableEntryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry with non-typed parameters "%s" -> "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
                'arg',
            ),
        );

        (new Builder())->add($configFile)->compile();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testCompileClassWithTypeHintedEnum(): void
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

        $files = [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$collectorClassName.php"];
        $configFile = $this->generateConfig(services: ['include' => $files]);

        $this->expectException(UninstantiableEntryException::class);
        $this->expectExceptionMessage(
            sprintf('Cannot instantiate entry with id "%s".', self::GENERATED_CLASS_NAMESPACE.$enumClassName),
        );

        (new Builder())->add($configFile)->compile();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
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

        $files = [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className.php"];
        $configFile = $this->generateConfig(services: ['include' => $files]);

        $container = (new Builder())->add($configFile)->compile();

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($object);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $object);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
     */
    public function testCompileWithCastedBoundVariablesFromAttributes(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly int $varOne,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly string $varTwo,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly float $varThree,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly bool $varFour,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_FLOAT_VAR)'),
                        'public readonly mixed $varFive,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_BOOL_VAL)'),
                        'public readonly mixed $varSix,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_INT_VAL)'),
                        'public readonly mixed $varSeven,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_STRING_VAL)'),
                        'public readonly mixed $varEight,',
                    ]),
            )
            ->generate();

        $configFile = $this->generateConfig(
            services: ['include' => [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className.'.php']],
        );

        $container = (new Builder())->add($configFile)->compile();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);

        self::assertEquals(10, $class->varOne);
        self::assertEquals('10.1', $class->varTwo);
        self::assertEquals(10.1, $class->varThree);
        self::assertTrue($class->varFour);
        self::assertEquals('10.1', $class->varFive);
        self::assertEquals('false', $class->varSix);
        self::assertEquals('3', $class->varSeven);
        self::assertEquals('string', $class->varEight);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
     */
    public function testCompileWithCastedBoundVariablesFromConfig(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly int $varOne,',
                        'public readonly string $varTwo,',
                        'public readonly float $varThree,',
                        'public readonly bool $varFour,',
                        'public readonly mixed $varFive,',
                        'public readonly mixed $varSix,',
                        'public readonly mixed $varSeven,',
                        'public readonly mixed $varEight,',
                    ]),
            )
            ->generate();

        $configFile = $this->generateConfig(
            services: ['include' => [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className.'.php']],
            classBindings: [
                self::GENERATED_CLASS_NAMESPACE.$className => [
                    'bind' => [
                        '$varOne'   => 'env(ENV_CASTABLE_STRING_VAR)',
                        'varTwo'    => 'env(ENV_CASTABLE_STRING_VAR)',
                        '$varThree' => 'env(ENV_CASTABLE_STRING_VAR)',
                        'varFour'   => 'env(ENV_CASTABLE_STRING_VAR)',
                        '$varFive'  => 'env(ENV_FLOAT_VAR)',
                        'varSix'    => 'env(ENV_BOOL_VAL)',
                        '$varSeven' => 'env(ENV_INT_VAL)',
                        'varEight'  => 'env(ENV_STRING_VAL)',
                    ],
                ],
            ],
        );

        $container = (new Builder())->add($configFile)->compile();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);

        self::assertEquals(10, $class->varOne);
        self::assertEquals('10.1', $class->varTwo);
        self::assertEquals(10.1, $class->varThree);
        self::assertTrue($class->varFour);
        self::assertEquals('10.1', $class->varFive);
        self::assertEquals('false', $class->varSix);
        self::assertEquals('3', $class->varSeven);
        self::assertEquals('string', $class->varEight);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testCompileWithCircularEnvVariableDependencies(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(CIRCULAR_ENV_VARIABLE_1)'),
                        'public readonly string $circular,',
                    ]),
            )
            ->generate();

        $configFile = $this->generateConfig(
            services: ['include' => [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className.'.php']],
        );

        $this->expectException(EnvVariableCircularException::class);
        $this->expectExceptionMessage(
            'Cannot resolve env variable "env(CIRCULAR_ENV_VARIABLE_2)" as '
            .'it has circular references "CIRCULAR_ENV_VARIABLE_1 -> CIRCULAR_ENV_VARIABLE_2".',
        );

        (new Builder())->add($configFile)->compile();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testCompileWithEnvVariableReferencingAnotherVariable(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_VARIABLE_REFERENCE)'),
                        'public readonly string $envReference,',
                    ]),
            )
            ->generate();

        $configFile = $this->generateConfig(
            services: ['include' => [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className.'.php']],
        );

        $container = (new Builder())->add($configFile)->compile();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertEquals('string_additional_string', $class->envReference);
    }

    public function testCompileWithNonExistentClass(): void
    {
        $classPath = self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.'NonExistentClass.php';
        $configFile = $this->generateConfig(services: ['include' => [$classPath]]);

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('The specified path "'.$classPath.'" does not exist.');

        (new Builder())->add($configFile);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
     */
    public function testCompilesWithCastingStringsFromAttribute(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, '10.1'),
                        'public readonly int $varOne,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, '10.1'),
                        'public readonly string $varTwo,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, '10.1'),
                        'public readonly float $varThree,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, '10.1'),
                        'public readonly bool $varFour,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, '10.1'),
                        'public readonly mixed $varFive,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'false'),
                        'public readonly mixed $varSix,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, '3'),
                        'public readonly mixed $varSeven,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'string'),
                        'public readonly mixed $varEight,',
                    ]),
            )
            ->generate();

        $configFile = $this->generateConfig(
            services: ['include' => [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className.'.php']],
        );

        $container = (new Builder())->add($configFile)->compile();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);

        self::assertEquals(10, $class->varOne);
        self::assertEquals('10.1', $class->varTwo);
        self::assertEquals(10.1, $class->varThree);
        self::assertTrue($class->varFour);
        self::assertEquals('10.1', $class->varFive);
        self::assertEquals('false', $class->varSix);
        self::assertEquals('3', $class->varSeven);
        self::assertEquals('string', $class->varEight);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
     */
    public function testCompilesWithCastingStringsFromConfig(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly int $varOne,',
                        'public readonly string $varTwo,',
                        'public readonly float $varThree,',
                        'public readonly bool $varFour,',
                        'public readonly mixed $varFive,',
                        'public readonly mixed $varSix,',
                        'public readonly mixed $varSeven,',
                        'public readonly mixed $varEight,',
                    ]),
            )
            ->generate();

        $configFile = $this->generateConfig(
            services: ['include' => [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className.'.php']],
            classBindings: [
                self::GENERATED_CLASS_NAMESPACE.$className => [
                    'bind' => [
                        '$varOne'   => '10.1',
                        'varTwo'    => '10.1',
                        '$varThree' => '10.1',
                        'varFour'   => '10.1',
                        '$varFive'  => '10.1',
                        'varSix'    => 'false',
                        '$varSeven' => '3',
                        'varEight'  => 'string',
                    ],
                ],
            ],
        );

        $container = (new Builder())->add($configFile)->compile();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);

        self::assertEquals(10, $class->varOne);
        self::assertEquals('10.1', $class->varTwo);
        self::assertEquals(10.1, $class->varThree);
        self::assertTrue($class->varFour);
        self::assertEquals('10.1', $class->varFive);
        self::assertEquals('false', $class->varSix);
        self::assertEquals('3', $class->varSeven);
        self::assertEquals('string', $class->varEight);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
     */
    public function testCompilesWithClassAliases(): void
    {
        $className = ClassGenerator::getClassName();
        $classFullNamespace = self::GENERATED_CLASS_NAMESPACE.$className;
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_ALIAS_SIGNATURE, 'empty_2'),
                        sprintf(self::ATTRIBUTE_ALIAS_SIGNATURE, 'empty2'),
                    ]),
            )
            ->generate();

        $configFile = $this->generateConfig(
            services: ['include' => [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className.'.php']],
        );

        $container = (new Builder())->add($configFile)->compile();

        $class = $container->get($classFullNamespace);

        self::assertInstanceOf($classFullNamespace, $class);
        self::assertTrue($container->has('empty_2'));
        self::assertTrue($container->has('empty2'));
        self::assertSame($class, $container->get('empty_2'));
        self::assertSame($class, $container->get('empty2'));
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
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
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$collectorClassName.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className1.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className2.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className3.'.php',
        ];

        $configFile = $this->generateConfig(services: ['include' => $files]);

        $container = (new Builder())->add($configFile)->compile();

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
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
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
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$interfaceName.'.php',
        ];

        $configFile = $this->generateConfig(
            services: ['include' => $classPaths],
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName => self::GENERATED_CLASS_NAMESPACE.$className,
            ],
        );

        $container = (new Builder())->add($configFile)->compile();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interfaceName, $class);
        self::assertTrue($container->has(self::GENERATED_CLASS_NAMESPACE.$interfaceName));
        self::assertSame($class, $container->get(self::GENERATED_CLASS_NAMESPACE.$interfaceName));
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
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
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$interfaceName3.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$interfaceName1.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$interfaceName2.'.php',
        ];

        $configFile = $this->generateConfig(services: ['include' => $classes]);

        $container = (new Builder())->add($configFile)->compile();

        $r = new ReflectionClass($container);

        /** @var DefinitionRepository $definitionRepository */
        $definitionRepository = $r->getProperty('definitionRepository')->getValue($container);

        $r = new ReflectionClass($definitionRepository);
        $definitions = $r->getProperty('definitions')->getValue($definitionRepository);

        /** @var Definition $classDefinition */
        $classDefinition = $definitions[self::GENERATED_CLASS_NAMESPACE.$className];

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
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
     */
    public function testCompilesWithMultipleEnvVarsInSingleBoundVariableFromAttribute(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_PARAMETER_SIGNATURE,
                            'env(ENV_VAR_1)env(ENV_VAR_2)_env(ENV_VAR_3)-TEST-env(ENV_VAR_4)',
                        ),
                        'public readonly string $arg,',
                    ]),
            )
            ->generate();

        $files = [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className.php"];

        $configFile = $this->generateConfig(services: ['include' => $files]);

        $container = (new Builder())->add($configFile)->compile();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className);

        self::assertIsObject($class);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className, $class);
        self::assertEquals('test_one10.1_test-three-TEST-true', $class->arg);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
     */
    public function testCompilesWithMultipleEnvVarsInSingleBoundVariableFromConfig(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments(['public readonly string $arg',]),
            )
            ->generate();

        $files = [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className.php"];

        $configFile = $this->generateConfig(
            services: ['include' => $files],
            classBindings: [
                self::GENERATED_CLASS_NAMESPACE.$className => [
                    'bind' => ['$arg' => 'env(ENV_VAR_1)env(ENV_VAR_2)_env(ENV_VAR_3)-TEST-env(ENV_VAR_4)'],
                ],
            ],
        );

        $container = (new Builder())->add($configFile)->compile();

        $fullClassNameSpace = self::GENERATED_CLASS_NAMESPACE.$className;
        $class = $container->get($fullClassNameSpace);

        self::assertIsObject($class);
        self::assertInstanceOf($fullClassNameSpace, $class);
        self::assertEquals('test_one10.1_test-three-TEST-true', $class->arg);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
     */
    public function testCompilesWithTaggedInterfaceImplementation(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        $classImplementingName1 = ClassGenerator::getClassName();
        $classImplementingName2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'Interface1')])
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classImplementingName1.php")
                    ->setName($classImplementingName1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classImplementingName2.php")
                    ->setName($classImplementingName2)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'Interface1'),
                        'public readonly iterable $dependency,',
                    ]),
            )
            ->generate();

        $classes = [
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$collectorClassName.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$interfaceName.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$classImplementingName1.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$classImplementingName2.'.php',
        ];

        $configFile = $this->generateConfig(services: ['include' => $classes]);

        $container = (new Builder())->add($configFile)->compile();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorClassName, $class);
        self::assertCount(2, $class->dependency);

        /** @psalm-suppress PossiblyInvalidArrayAccess,UndefinedInterfaceMethod */
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classImplementingName1, $class->dependency[0]);

        /** @psalm-suppress PossiblyInvalidArrayAccess,UndefinedInterfaceMethod */
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$classImplementingName2, $class->dependency[1]);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
     */
    public function testCompilesWithTaggedIteratorFromAttribute(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $taggedClassName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'tag_1'),
                        'public readonly array $dependency1,',
                        sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'tag_1'),
                        'public readonly iterable $dependency2,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$taggedClassName.php")
                    ->setName($taggedClassName)
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'tag_1')]),
            )
            ->generate();

        $classes = [
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$collectorClassName.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$taggedClassName.'.php',
        ];

        $configFile = $this->generateConfig(services: ['include' => $classes]);

        $container = (new Builder())->add($configFile)->compile();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorClassName, $class);
        self::assertIsArray($class->dependency1);
        self::assertIsArray($class->dependency2);

        self::assertCount(1, $class->dependency1);
        self::assertCount(1, $class->dependency2);

        self::assertIsObject($class->dependency1[0]);
        self::assertIsObject($class->dependency2[0]);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$taggedClassName, $class->dependency1[0]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$taggedClassName, $class->dependency2[0]);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
     */
    public function testCompilesWithTaggedIteratorFromConfig(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $taggedClassName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        'public readonly array $dependency1,',
                        'public readonly iterable $dependency2,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$taggedClassName.php")
                    ->setName($taggedClassName),
            )
            ->generate();

        $classes = [
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$collectorClassName.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$taggedClassName.'.php',
        ];

        $configFile = $this->generateConfig(
            services: ['include' => $classes],
            classBindings: [
                self::GENERATED_CLASS_NAMESPACE.$collectorClassName => [
                    'bind' => [
                        'dependency1' => '!tagged empty_2',
                        'dependency2' => '!tagged empty_2',
                    ],
                ],
                self::GENERATED_CLASS_NAMESPACE.$taggedClassName    => [
                    'tags' => ['empty_2'],
                ],
            ],
        );

        $container = (new Builder())->add($configFile)->compile();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorClassName);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$collectorClassName, $class);
        self::assertIsArray($class->dependency1);
        self::assertIsArray($class->dependency2);

        self::assertCount(1, $class->dependency1);
        self::assertCount(1, $class->dependency2);

        self::assertIsObject($class->dependency1[0]);
        self::assertIsObject($class->dependency2[0]);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$taggedClassName, $class->dependency1[0]);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$taggedClassName, $class->dependency2[0]);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     *
     * @dataProvider getDataForCompilesWithUninstantiableEntryTest
     */
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

        $classPath = self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className.'.php';
        $configFile = $this->generateConfig(services: ['include' => [$classPath]]);

        $container = (new Builder())->add($configFile)->compile();

        self::assertFalse($container->has($className));
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
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

        $files = [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$collectorName.'.php'];

        $configFile = $this->generateConfig(services: ['include' => $files]);

        $container = (new Builder())->add($configFile)->compile();

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

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$collectorName);
        self::assertIsObject($object->dependency1);
        self::assertIsObject($object->dependency2);
        self::assertIsObject($object->dependency3);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $object->dependency1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $object->dependency2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $object->dependency3);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection,UnnecessaryAssertionInspection
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

        $files = [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className.php"];

        $configFile = $this->generateConfig(services: ['include' => $files]);

        $container = (new Builder())->add($configFile)->compile();

        $classFullNamespace = self::GENERATED_CLASS_NAMESPACE.$className;

        $class = $container->get($classFullNamespace);
        self::assertInstanceOf($classFullNamespace, $class);
        self::assertNull($class->arg);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testDoesNotCompileDueToCircularExceptionByTaggedBinding(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'circular')])
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_TAGGED_SIGNATURE, 'circular'),
                        'public readonly iterable $arg',
                    ]),
            )
            ->generate();

        $files = [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className.php"];

        $configFile = $this->generateConfig(services: ['include' => $files]);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(CircularReferenceException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate class "%s" as it has circular references "%s".',
                $className,
                $className,
            ),
        );

        (new Builder())->add($configFile)->compile();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     *
     * @dataProvider getDataForDoesNotCompileDueToInternalClassDependencyTest
     */
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

        $files = [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className.php"];
        $configFile = $this->generateConfig(services: ['include' => $files]);

        $this->expectException(UninstantiableEntryException::class);
        $this->expectExceptionMessage(sprintf('Cannot resolve internal entry "%s".', $argumentClassName));

        (new Builder())->add($configFile)->compile();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testDoesNotCompileDueToNonExistentBoundVariable(): void
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

        $files = [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className.php"];

        $configFile = $this->generateConfig(services: ['include' => $files]);

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "%s::%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
                'age',
                'int',
            ),
        );

        (new Builder())->add($configFile)->compile();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     *
     * @dataProvider getDataForDoesNotCompileDueToNotDeterminedArgumentTypeTest
     */
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

        $configFile = $this->generateConfig(
            services: ['include' => [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className.'.php']],
        );

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        (new Builder())->add($configFile)->compile();
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection,PhpUnhandledExceptionInspection
     *
     * @dataProvider getDataForDoesNotCompileDueToVariableBindingErrorsTest
     *
     * @param class-string<Throwable> $exception
     */
    public function testDoesNotCompileDueToVariableBindingErrors(
        string $className,
        array $constructorArguments,
        string $exception,
        string $exceptionMessage,
    ): void {
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments($constructorArguments),
            )
            ->generate();

        $files = [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className.php"];
        $configFile = $this->generateConfig(
            services: ['include' => $files],
            classBindings: [
                self::GENERATED_CLASS_NAMESPACE.$className => ['bind' => ['$arg' => 'env(ENV_STRING_VAR)']],
            ],
        );

        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        (new Builder())->add($configFile)->compile();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
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
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$collectorName.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className1.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className2.'.php',
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className3.'.php',
        ];
        $excludeFiles = [
            self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$className3.'.php',
        ];

        $configFile = $this->generateConfig(services: ['include' => $includeFiles, 'exclude' => $excludeFiles]);

        $this->expectException(NonAutowirableClassException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot autowire class "%s" as it is in "exclude" config parameter.',
                self::GENERATED_CLASS_NAMESPACE.$className3,
            ),
        );

        (new Builder())->add($configFile)->compile();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testDoesNotCompileWithNonAutowirableAttributeClass(): void
    {
        $collectorClassName = ClassGenerator::getClassName();
        $collectorClassPath = self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$collectorClassName.'.php';
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
                    ->setAttributes([self::ATTRIBUTE_NON_AUTOWIRABLE_SIGNATURE]),
            )
            ->generate();

        $configFile = $this->generateConfig(services: ['include' => [$collectorClassPath]]);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NonAutowirableClassException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Class "%s" has NonAutowirable attribute and cannot be autowired.',
                self::GENERATED_CLASS_NAMESPACE.$invalidClassName,
            ),
        );

        (new Builder())->add($configFile)->compile();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     *
     * @dataProvider getDataForDoesNotCompileWithUninstantiableEntryTest
     */
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

        $classPath = self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$collectorClassName.'.php';
        $configFile = $this->generateConfig(services: ['include' => [$classPath]]);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(UninstantiableEntryException::class);
        $this->expectExceptionMessage(
            sprintf('Cannot instantiate entry with id "%s".', self::GENERATED_CLASS_NAMESPACE.$invalidClassName),
        );

        (new Builder())->add($configFile)->compile();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testWithoutCompiling(): void
    {
        $container = (new Builder())->compile();

        $this->expectException(EntryNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Could not find entry "%s".', 'asd'));

        $container->get('asd');
    }

    private function generateConfig(
        array $services = [],
        array $interfaceBindings = [],
        array $classBindings = [],
    ): SplFileInfo {
        $config = [];

        if ($services) {
            $config['services'] = $services;
        }

        if ($interfaceBindings) {
            $config['interface_bindings'] = $interfaceBindings;
        }

        if ($classBindings) {
            $config['class_bindings'] = $classBindings;
        }

        $configPath = realpath(__DIR__.self::GENERATED_CONFIG_STUB_PATH).'/config.yaml';
        file_put_contents(
            $configPath,
            Yaml::dump($config),
        );

        return new SplFileInfo($configPath);
    }
}

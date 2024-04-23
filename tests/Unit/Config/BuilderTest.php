<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Temkaa\SimpleContainer\Container\Builder;
use Temkaa\SimpleContainer\Exception\Config\EntryNotFoundException as ConfigEntryNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\EnvVariableNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;
use Temkaa\SimpleContainer\Exception\Config\InvalidPathException;
use Temkaa\SimpleContainer\Exception\EntryNotFoundException;
use Temkaa\SimpleContainer\Model\Container\Config;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class BuilderTest extends AbstractBuilderTest
{
    /**
     * @var class-string $classWithBuiltInArgumentTypesNamespace
     */
    private readonly string $classWithBuiltInArgumentTypesNamespace;

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testBuild(): void
    {
        // TODO: optimize generation process do we need THAT amount of files?(
        [$configContent, $configFile] = $this->generateConfig(
            classBindings: [
                $this->classWithBuiltInArgumentTypesNamespace => [
                    'bind' => [
                        '$string' => 'string',
                        '$float'  => '3.14',
                    ],
                    'tags' => ['tag_1', 'tag_2'],
                ],
            ],
        );

        $builder = (new Builder())->add($configFile);

        $config = $this->getConfigContent($builder);

        $autowiredClassNames = array_map(
            static fn (string $classPath): string => str_replace(
                [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH, '.php'],
                '',
                $classPath,
            ),
            $configContent['services']['include'],
        );
        $nonAutowiredClassNames = array_map(
            static fn (string $classPath): string => str_replace(
                [
                    self::GENERATED_CLASS_CONFIG_RELATIVE_PATH,
                    '.php',
                ],
                '',
                $classPath,
            ),
            $configContent['services']['exclude'],
        );

        $includedClassesNamespaces = array_map(
            static fn (string $className): string => self::GENERATED_CLASS_NAMESPACE.$className,
            $autowiredClassNames,
        );
        $excludedClassesNamespaces = array_map(
            static fn (string $className): string => self::GENERATED_CLASS_NAMESPACE.$className,
            $nonAutowiredClassNames,
        );

        self::assertEquals($includedClassesNamespaces, $config->getIncludedClasses());
        self::assertEquals($excludedClassesNamespaces, $config->getExcludedClasses());

        /** @var class-string $interfaceName */
        $interfaceName = array_keys($configContent['interface_bindings'])[0];
        $interfaceImplementationName = array_values($configContent['interface_bindings'])[0];

        self::assertEquals(
            $interfaceImplementationName,
            $config->getInterfaceImplementation($interfaceName),
        );
        self::assertEquals(
            [
                'string' => 'string',
                'float'  => '3.14',
            ],
            $config->getClassBoundVariables($this->classWithBuiltInArgumentTypesNamespace),
        );
        self::assertEquals(
            ['tag_1', 'tag_2'],
            $config->getClassTags($this->classWithBuiltInArgumentTypesNamespace),
        );
    }

    public function testBuildWithInvalidConfigExtension(): void
    {
        $configFile = new SplFileInfo(__FILE__);

        $this->expectException(InvalidConfigNodeTypeException::class);
        $this->expectExceptionMessage('Config file must have .yaml extension.');

        (new Builder())->add($configFile);
    }

    public function testBuildWithInvalidConfigFilePath(): void
    {
        $configFile = new SplFileInfo('/non_existing_configPath/file.yaml');

        $this->expectException(ConfigEntryNotFoundException::class);
        $this->expectExceptionMessage('Could not find container config in path "/non_existing_configPath/file.yaml".');

        (new Builder())->add($configFile);
    }

    /**
     * @param class-string<Throwable> $exceptionClass
     *
     * @dataProvider getDataForInterfaceBindingErrorsTest
     */
    public function testConfigDoesNotInitDueToInterfaceBindingErrors(
        array $config,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $configFile = $this->generateConfig(interfaceBindings: $config)[1];

        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($exceptionMessage);

        (new Builder())->add($configFile);
    }

    public function testConfigDoesNotInitDueToInvalidServicePath(): void
    {
        $configFile = $this->generateConfig(services: ['exclude' => ['src/Factory/']])[1];

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('The specified path "src/Factory/" does not exist.');

        (new Builder())->add($configFile);
    }

    /**
     * @param class-string<Throwable> $exceptionClass
     *
     * @dataProvider getDataForIncorrectConfigNodeTypesTest
     */
    public function testConfigDoesNotLoadDueToIncorrectConfigNodeTypes(
        mixed $services,
        mixed $interfaceBindings,
        mixed $classBindings,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $configFile = $this->generateConfig($services, $interfaceBindings, $classBindings)[1];

        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($exceptionMessage);

        (new Builder())->add($configFile);
    }

    public function testConfigHasEnvBoundVariables(): void
    {
        $configFile = $this->generateConfig(
            classBindings: [
                $this->classWithBuiltInArgumentTypesNamespace => ['bind' => ['string' => 'env(APP_BOUND_VAR)']],
            ],
        )[1];

        $builder = (new Builder())->add($configFile);

        $config = $this->getConfigContent($builder);

        self::assertEquals(
            [
                'string' => 'bound_variable_value',
            ],
            $config->getClassBoundVariables($this->classWithBuiltInArgumentTypesNamespace),
        );
    }

    public function testConfigHasNoBoundVariables(): void
    {
        $configFile = $this->generateConfig()[1];

        $builder = (new Builder())->add($configFile);

        $config = $this->getConfigContent($builder);

        /** @psalm-suppress ArgumentTypeCoercion, UndefinedClass */
        self::assertEmpty($config->getClassBoundVariables('NonExistentClass'));
    }

    public function testConfigHasNoTagsForClass(): void
    {
        $configFile = $this->generateConfig()[1];

        $builder = (new Builder())->add($configFile);

        $config = $this->getConfigContent($builder);

        /** @psalm-suppress ArgumentTypeCoercion, UndefinedClass */
        self::assertEmpty($config->getClassTags('NonExistentClass'));
    }

    public function testConfigHasNonExistentEnvBoundVariables(): void
    {
        $configFile = $this->generateConfig(
            classBindings: [
                $this->classWithBuiltInArgumentTypesNamespace => ['bind' => ['string' => 'env(APP_DEBUG)']],
            ],
        )[1];

        $this->expectException(EnvVariableNotFoundException::class);
        $this->expectExceptionMessage('Variable "APP_DEBUG" is not found in env variables.');

        (new Builder())->add($configFile);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testConfigHasNotInterfaceImplementation(): void
    {
        $configFile = $this->generateConfig()[1];

        $builder = (new Builder())->add($configFile);

        $config = $this->getConfigContent($builder);

        $this->expectExceptionMessage(EntryNotFoundException::class);
        $this->expectExceptionMessage('Entry "NonExistentInterface" not found.');

        /** @psalm-suppress ArgumentTypeCoercion, UndefinedClass */
        $config->getInterfaceImplementation('NonExistentInterface');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $classWithBuiltInArgumentTypesName = 'TestClass'.self::getNextGeneratedClassNumber();
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$classWithBuiltInArgumentTypesName.php",
            className: $classWithBuiltInArgumentTypesName,
            hasConstructor: true,
            constructorArguments: [
                'public readonly bool $bool,',
                'public readonly float $float,',
                'public readonly int $int,',
                'public readonly string $string,',
                'public readonly mixed $mixed,',
            ],
        );

        /** @psalm-suppress InaccessibleProperty, PropertyTypeCoercion */
        $this->classWithBuiltInArgumentTypesNamespace = self::GENERATED_CLASS_NAMESPACE.$classWithBuiltInArgumentTypesName;
    }

    private function generateConfig(
        mixed $services = [],
        mixed $interfaceBindings = [],
        mixed $classBindings = [],
    ): array {
        $emptyClassName1 = 'TestClass'.self::getNextGeneratedClassNumber();
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$emptyClassName1.php",
            className: $emptyClassName1,
        );
        $emptyClassName2 = 'TestClass'.self::getNextGeneratedClassNumber();
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$emptyClassName2.php",
            className: $emptyClassName2,
        );
        $emptyClassName3 = 'TestClass'.self::getNextGeneratedClassNumber();
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$emptyClassName3.php",
            className: $emptyClassName3,
        );

        $interfaceName = 'TestClass'.self::getNextGeneratedClassNumber();
        $interfaceNamespace = self::GENERATED_CLASS_NAMESPACE.$interfaceName;
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php",
            className: $interfaceName,
            classNamePrefix: 'interface',
        );
        $interfaceImplementationName = 'TestClass'.self::getNextGeneratedClassNumber();
        $interfaceImplementationNamespace = self::GENERATED_CLASS_NAMESPACE.$interfaceImplementationName;
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceImplementationName.php",
            className: $interfaceImplementationName,
            interfacesImplements: [self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName],
        );

        $config = [
            'services'           => [
                'include' => [
                    self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$emptyClassName1.'.php',
                    self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$emptyClassName2.'.php',
                ],
                'exclude' => [
                    self::GENERATED_CLASS_CONFIG_RELATIVE_PATH.$emptyClassName3.'.php',
                ],
            ],
            'interface_bindings' => [
                $interfaceNamespace => $interfaceImplementationNamespace,
            ],
            'class_bindings'     => [
                $this->classWithBuiltInArgumentTypesNamespace => [
                    'bind' => [
                        '$string' => 'string',
                        '$float'  => '3.14',
                    ],
                ],
            ],
        ];

        if ($services !== []) {
            $config['services'] = $services;
        }

        if ($interfaceBindings !== []) {
            $config['interface_bindings'] = $interfaceBindings;
        }

        if ($classBindings !== []) {
            $config['class_bindings'] = $classBindings;
        }

        $configPath = realpath(__DIR__.self::GENERATED_CONFIG_STUB_PATH).'/config.yaml';
        file_put_contents(
            $configPath,
            Yaml::dump($config),
        );

        return [$config, new SplFileInfo($configPath)];
    }

    private function getConfigContent(Builder $builder): Config
    {
        $r = new ReflectionClass($builder);

        return $r->getProperty('configs')->getValue($builder)[0];
    }
}

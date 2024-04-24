<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Temkaa\SimpleContainer\Container\Builder;
use Temkaa\SimpleContainer\Enum\Config\Structure;
use Temkaa\SimpleContainer\Exception\Config\EntryNotFoundException as ConfigEntryNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\EnvVariableNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;
use Temkaa\SimpleContainer\Exception\Config\InvalidPathException;
use Temkaa\SimpleContainer\Exception\EntryNotFoundException;
use Temkaa\SimpleContainer\Model\Container\Config;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class BuilderTest extends AbstractBuilderTestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuild(): void
    {
        $className1 = ClassGenerator::getClassName();
        $classFullNamespace1 = self::GENERATED_CLASS_NAMESPACE.$className1;
        $className2 = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        $interfaceFullNamespace = self::GENERATED_CLASS_NAMESPACE.$interfaceName;
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
                    ->setName($className2),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->generate();

        /** @psalm-suppress PossiblyInvalidArrayAccess */
        [$configContent, $configFile] = $this->generateConfig(
            services: [
                Structure::Include->value => [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className1.php"],
                Structure::Exclude->value => [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className2.php"],
            ],
            globalBindings: [
                '$variableOne' => 'env(APP_BOUND_VAR)',
                '$variableTwo' => 'env(ENV_STRING_VAL)',
            ],
            interfaceBindings: [$interfaceFullNamespace => $classFullNamespace1],
            classBindings: [
                $classFullNamespace1 => [
                    Structure::Bind->value => [
                        '$string' => 'string',
                        '$float'  => '3.14',
                    ],
                    Structure::Tags->value => ['tag_1', 'tag_2'],
                ],
            ],
            withConfig: true,
        );

        $builder = (new Builder())->add($configFile);

        $config = $this->getConfigContent($builder);

        $autowiredClassNames = array_map(
            static fn (string $classPath): string => str_replace(
                [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH, '.php'],
                '',
                $classPath,
            ),
            $configContent[Structure::Services->value][Structure::Include->value],
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
            $configContent[Structure::Services->value][Structure::Exclude->value],
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

        $interface = current(
            array_values(
                array_filter(
                    array_keys($configContent[Structure::Services->value]),
                    static fn (array|string $interfaceName): bool => is_string($interfaceName) && interface_exists(
                            $interfaceName,
                        ),
                ),
            ),
        );

        /** @psalm-suppress InvalidArrayOffset */
        self::assertEquals(
            [$interface => $configContent[Structure::Services->value][$interface]],
            $config->getInterfaceImplementations(),
        );

        /** @psalm-suppress ArgumentTypeCoercion */
        self::assertEquals(
            [
                'string' => 'string',
                'float'  => '3.14',
            ],
            $config->getClassBoundVariables($classFullNamespace1),
        );

        /** @psalm-suppress ArgumentTypeCoercion */
        self::assertEquals(
            ['tag_1', 'tag_2'],
            $config->getClassTags($classFullNamespace1),
        );

        self::assertEquals(
            [
                'variableOne' => 'bound_variable_value',
                'variableTwo' => 'string',
            ],
            $config->getGlobalBoundVariables(),
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
        $configFile = $this->generateConfig(interfaceBindings: $config);

        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($exceptionMessage);

        /** @psalm-suppress PossiblyInvalidArgument */
        (new Builder())->add($configFile);
    }

    public function testConfigDoesNotInitDueToInvalidServicePath(): void
    {
        $configFile = $this->generateConfig(services: [Structure::Exclude->value => ['src/Factory/']]);

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('The specified path "src/Factory/" does not exist.');

        /** @psalm-suppress PossiblyInvalidArgument */
        (new Builder())->add($configFile);
    }

    /**
     * @param class-string<Throwable> $exceptionClass
     *
     * @dataProvider getDataForIncorrectConfigNodeTypesTest
     */
    public function testConfigDoesNotLoadDueToIncorrectConfigNodeTypes(
        mixed $services,
        mixed $globalBindings,
        mixed $interfaceBindings,
        mixed $classBindings,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $configFile = $this->generateConfig($services, $globalBindings, $interfaceBindings, $classBindings);

        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($exceptionMessage);

        /** @psalm-suppress PossiblyInvalidArgument */
        (new Builder())->add($configFile);
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

        $configFile = $this->generateConfig(
            classBindings: [
                $classFullNamespace => [Structure::Bind->value => ['string' => 'env(APP_BOUND_VAR)']],
            ],
        );

        /** @psalm-suppress PossiblyInvalidArgument */
        $builder = (new Builder())->add($configFile);

        $config = $this->getConfigContent($builder);

        /** @psalm-suppress ArgumentTypeCoercion */
        self::assertEquals(
            [
                'string' => 'bound_variable_value',
            ],
            $config->getClassBoundVariables($classFullNamespace),
        );
    }

    public function testConfigHasNoBoundVariables(): void
    {
        $configFile = $this->generateConfig();

        /** @psalm-suppress PossiblyInvalidArgument */
        $builder = (new Builder())->add($configFile);

        $config = $this->getConfigContent($builder);

        /** @psalm-suppress ArgumentTypeCoercion, UndefinedClass */
        self::assertEmpty($config->getClassBoundVariables('NonExistentClass'));
    }

    public function testConfigHasNoTagsForClass(): void
    {
        $configFile = $this->generateConfig();

        /** @psalm-suppress PossiblyInvalidArgument */
        $builder = (new Builder())->add($configFile);

        $config = $this->getConfigContent($builder);

        /** @psalm-suppress ArgumentTypeCoercion, UndefinedClass */
        self::assertEmpty($config->getClassTags('NonExistentClass'));
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

        $configFile = $this->generateConfig(
            classBindings: [
                self::GENERATED_CLASS_NAMESPACE.$className => [Structure::Bind->value => ['string' => 'env(APP_DEBUG)']],
            ],
        );

        $this->expectException(EnvVariableNotFoundException::class);
        $this->expectExceptionMessage('Variable "APP_DEBUG" is not found in env variables.');

        /** @psalm-suppress PossiblyInvalidArgument */
        (new Builder())->add($configFile);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testConfigHasNotInterfaceImplementation(): void
    {
        /** @psalm-suppress PossiblyInvalidArgument */
        $builder = (new Builder())->add($this->generateConfig());

        $config = $this->getConfigContent($builder);

        $this->expectExceptionMessage(EntryNotFoundException::class);
        $this->expectExceptionMessage('Entry "NonExistentInterface" not found.');

        /** @psalm-suppress ArgumentTypeCoercion, UndefinedClass */
        $config->getInterfaceImplementation('NonExistentInterface');
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function generateConfig(
        mixed $services = [],
        mixed $globalBindings = [],
        mixed $interfaceBindings = [],
        mixed $classBindings = [],
        bool $withConfig = false,
    ): array|SplFileInfo {

        $config = [Structure::Services->value => []];

        if ($services !== []) {
            $config[Structure::Services->value] = $services;
        }

        if ($globalBindings !== []) {
            $config[Structure::Services->value][Structure::Bind->value] = $globalBindings;
        }

        if ($interfaceBindings !== []) {
            if (is_array($interfaceBindings)) {
                foreach ($interfaceBindings as $interfaceName => $className) {
                    $config[Structure::Services->value][$interfaceName] = $className;
                }
            } else {
                $config[Structure::Services->value][] = $interfaceBindings;
            }
        }

        if ($classBindings !== []) {
            if (is_array($classBindings)) {
                foreach ($classBindings as $className => $classInfo) {
                    $config[Structure::Services->value][$className] = $classInfo;
                }
            } else {
                $config[Structure::Services->value][] = $classBindings;
            }
        }

        $configPath = realpath(__DIR__.self::GENERATED_CONFIG_STUB_PATH).'/config.yaml';
        file_put_contents(
            $configPath,
            Yaml::dump($config),
        );

        $configFile = new SplFileInfo($configPath);

        return $withConfig ? [$config, $configFile] : $configFile;
    }

    private function getConfigContent(Builder $builder): Config
    {
        $r = new ReflectionClass($builder);

        return $r->getProperty('configs')->getValue($builder)[0];
    }
}

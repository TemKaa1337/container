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
        $interfaceAbsoluteNamespace = self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName;
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([$interfaceAbsoluteNamespace]),
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

        [$configContent, $configFile] = $this->generateConfig(
            services: [
                'include' => [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className1.php"],
                'exclude' => [self::GENERATED_CLASS_CONFIG_RELATIVE_PATH."$className2.php"],
            ],
            interfaceBindings: [$interfaceAbsoluteNamespace => $classFullNamespace1],
            classBindings: [
                $classFullNamespace1 => [
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
                $classFullNamespace => ['bind' => ['string' => 'env(APP_BOUND_VAR)']],
            ],
        )[1];

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
                self::GENERATED_CLASS_NAMESPACE.$className => ['bind' => ['string' => 'env(APP_DEBUG)']],
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

    private function generateConfig(
        mixed $services = [],
        mixed $interfaceBindings = [],
        mixed $classBindings = [],
    ): array {

        $config = [];

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

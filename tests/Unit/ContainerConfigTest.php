<?php

declare(strict_types=1);

namespace Tests\Unit;

use Temkaa\SimpleContainer\Config;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\CannotBindInterfaceException;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;
use Temkaa\SimpleContainer\Exception\Config\InvalidPathException;
use Temkaa\SimpleContainer\Exception\EntryNotFoundException;
use Temkaa\SimpleContainer\Exception\EnvVariableNotFoundException;

final class ContainerConfigTest extends AbstractUnitTestCase
{
    private readonly string $classWithBuiltInArgumentTypesNamespace;

    public static function getDataForIncorrectConfigNodeTypesTest(): iterable
    {
        $emptyClassName = 'TestClass'.self::getNextGeneratedClassNumber();
        $emptyClassNamespace = self::GENERATED_CLASS_NAMESPACE.$emptyClassName;
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$emptyClassName.php",
            className: $emptyClassName,
        );

        $invalidTypes = [
            10,
            10.1,
            static fn (): string => '',
            new $emptyClassNamespace,
            true,
            [],
        ];

        yield [[], InvalidConfigNodeTypeException::class, 'Node "config_dir" must be of "string" type.'];

        foreach ($invalidTypes as $invalidType) {
            yield [
                ['config_dir' => $invalidType],
                InvalidConfigNodeTypeException::class,
                'Node "config_dir" must be of "string" type.',
            ];
        }

        $baseConfig = ['config_dir' => __DIR__];

        $invalidTypes = [
            10,
            10.1,
            static fn (): string => '',
            new $emptyClassNamespace,
            true,
            '',
        ];

        foreach ($invalidTypes as $invalidType) {
            $baseConfig['services'] = $invalidType;

            yield [
                $baseConfig,
                InvalidConfigNodeTypeException::class,
                'Node "services" must be of "array<include|exclude, array>" type.',
            ];
        }

        $invalidTypes = [
            10,
            10.1,
            static fn (): string => '',
            new $emptyClassNamespace,
            true,
            '',
        ];

        foreach (['include', 'exclude'] as $key) {
            foreach ($invalidTypes as $invalidType) {
                $baseConfig['services'] = [$key => $invalidType];

                yield [
                    $baseConfig,
                    InvalidConfigNodeTypeException::class,
                    sprintf('services.%s" must be of "array<int, array>" type.', $key),
                ];
            }
        }

        unset($baseConfig['services']);

        foreach ($invalidTypes as $invalidType) {
            $baseConfig['interface_bindings'] = $invalidType;

            yield [
                $baseConfig,
                InvalidConfigNodeTypeException::class,
                'Node "interface_bindings" must be of "array<string, string>" type.',
            ];
        }

        $invalidTypes = [
            10,
            10.1,
            true,
        ];

        foreach ($invalidTypes as $invalidType) {
            $baseConfig['interface_bindings'] = [$invalidType => $invalidType];

            yield [
                $baseConfig,
                InvalidConfigNodeTypeException::class,
                'Node "interface_bindings" must be of "array<string, string>" type.',
            ];
        }

        $invalidTypes = [
            10,
            10.1,
            static fn (): string => '',
            new $emptyClassNamespace,
            true,
        ];

        foreach ($invalidTypes as $invalidType) {
            $baseConfig['interface_bindings'] = ['interface' => $invalidType];

            yield [
                $baseConfig,
                InvalidConfigNodeTypeException::class,
                'Node "interface_bindings" must be of "array<string, string>" type.',
            ];
        }

        unset($baseConfig['interface_bindings']);

        $invalidTypes = [
            10,
            10.1,
            static fn (): string => '',
            new $emptyClassNamespace,
            true,
        ];

        foreach ($invalidTypes as $invalidType) {
            $baseConfig['class_bindings'] = $invalidType;

            yield [
                $baseConfig,
                InvalidConfigNodeTypeException::class,
                'Node "class_bindings" must be of "array<string, array>" type.',
            ];
        }

        $emptyClassName = 'TestClass'.self::getNextGeneratedClassNumber();
        $emptyClassNamespace = self::GENERATED_CLASS_NAMESPACE.$emptyClassName;
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$emptyClassName.php",
            className: $emptyClassName,
        );

        foreach ($invalidTypes as $invalidType) {
            $baseConfig['class_bindings'] = [$emptyClassNamespace => $invalidType];

            yield [
                $baseConfig,
                InvalidConfigNodeTypeException::class,
                'Node "class_bindings" must be of "array<string, array>" type.',
            ];
        }

        foreach ($invalidTypes as $invalidType) {
            $baseConfig['class_bindings'] = [$emptyClassNamespace => ['bind' => $invalidType]];

            yield [
                $baseConfig,
                InvalidConfigNodeTypeException::class,
                'Node "class_bindings.{className}.bind" must be of "array<string, string>" type.',
            ];
        }

        $invalidTypes[] = ['key' => 'value'];

        foreach ($invalidTypes as $invalidType) {
            $baseConfig['class_bindings'] = [$emptyClassNamespace => ['tags' => $invalidType]];

            yield [
                $baseConfig,
                InvalidConfigNodeTypeException::class,
                'Node "class_bindings.{className}.tags" must be of "array<int, string>" type.',
            ];
        }

        foreach ($invalidTypes as $invalidType) {
            $baseConfig['class_bindings'] = [$emptyClassNamespace => ['tags' => [$invalidType]]];

            yield [
                $baseConfig,
                InvalidConfigNodeTypeException::class,
                'Node "class_bindings.{className}.tags" must be of "array<int, string>" type.',
            ];
        }
    }

    public static function getDataForInterfaceBindingErrorsTest(): iterable
    {
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

        yield [
            ['NonExistentInterface' => $interfaceImplementationNamespace],
            ClassNotFoundException::class,
            sprintf('Class "%s" is not found.', 'NonExistentInterface'),
        ];

        yield [
            [$interfaceNamespace => 'NonExistentInterfaceImplementation'],
            ClassNotFoundException::class,
            sprintf('Class "%s" is not found.', 'NonExistentInterfaceImplementation'),
        ];

        $emptyClassName = 'TestClass'.self::getNextGeneratedClassNumber();
        $emptyClassNamespace = self::GENERATED_CLASS_NAMESPACE.$emptyClassName;
        self::generateClass(
            absolutePath: realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$emptyClassName.php",
            className: $emptyClassName,
        );
        yield [
            [$interfaceNamespace => $emptyClassNamespace],
            CannotBindInterfaceException::class,
            sprintf(
                'Cannot bind interface "%s" to class "%s" as it doesn\'t implement int.',
                $interfaceNamespace,
                $emptyClassNamespace,
            ),
        ];
    }

    /**
     * @dataProvider getDataForInterfaceBindingErrorsTest
     */
    public function testConfigDoesNotInitDueToInterfaceBindingErrors(
        array $config,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $config = $this->getConfig(interfaceBindings: $config);

        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($exceptionMessage);

        new Config($config, env: []);
    }

    public function testConfigDoesNotInitDueToInvalidServicePath(): void
    {
        $config = $this->getConfig(services: ['exclude' => ['src/Factory/']]);

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('The specified path "src/Factory/" does not exist.');

        new Config($config, env: []);
    }

    /**
     * @dataProvider getDataForIncorrectConfigNodeTypesTest
     */
    public function testConfigDoesNotLoadDueToIncorrectConfigNodeTypes(
        array $config,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($exceptionMessage);

        new Config($config, env: []);
    }

    public function testConfigHasEnvBoundVariables(): void
    {
        $configVars = $this->getConfig(
            classBindings: [
                $this->classWithBuiltInArgumentTypesNamespace => ['bind' => ['string' => 'env(APP_BOUND_VAR)']],
            ],
        );

        $env = ['APP_BOUND_VAR' => 'bound_variable_value'];

        $config = new Config($configVars, $env);

        self::assertEquals(
            [
                'string' => 'bound_variable_value',
            ],
            $config->getClassBoundVariables($this->classWithBuiltInArgumentTypesNamespace),
        );
    }

    public function testConfigHasNoBoundVariables(): void
    {
        $configVars = $this->getConfig();

        $config = new Config($configVars, env: []);

        self::assertIsObject($config);

        $this->assertEmpty($config->getClassBoundVariables('NonExistentClass'));
    }

    public function testConfigHasNoTagsForClass(): void
    {
        $configVars = $this->getConfig();

        $config = new Config($configVars, env: []);

        self::assertIsObject($config);

        $this->assertEmpty($config->getClassTags('NonExistentClass'));
    }

    public function testConfigHasNonExistentEnvBoundVariables(): void
    {
        $config = $this->getConfig(
            classBindings: [
                $this->classWithBuiltInArgumentTypesNamespace => ['bind' => ['string' => 'env(APP_DEBUG)']],
            ],
        );

        $this->expectException(EnvVariableNotFoundException::class);
        $this->expectExceptionMessage('Variable "APP_DEBUG" is not found in dov env variables.');

        new Config($config, env: []);
    }

    public function testConfigHasNotInterfaceImplementation(): void
    {
        $config = $this->getConfig();
        $config = new Config($config, env: []);

        self::assertIsObject($config);

        $this->expectExceptionMessage(EntryNotFoundException::class);
        $this->expectExceptionMessage('Entry "NonExistentInterface" not found.');

        $config->getInterfaceImplementation('NonExistentInterface');
    }

    public function testConfigLoads(): void
    {
        $configVars = $this->getConfig(
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

        $config = new Config($configVars, env: []);

        self::assertIsObject($config);

        $autowiredClassNames = array_map(
            static fn (string $classPath): string => str_replace([self::GENERATED_CLASS_STUB_PATH, '.php'],
                '',
                $classPath),
            $configVars['services']['include'],
        );
        $nonAutowiredClassNames = array_map(
            static fn (string $classPath): string => str_replace([self::GENERATED_CLASS_STUB_PATH, '.php'],
                '',
                $classPath),
            $configVars['services']['exclude'],
        );

        $autowiredClassNamespaces = array_map(
            static fn (string $className): string => self::GENERATED_CLASS_NAMESPACE.$className,
            $autowiredClassNames,
        );
        $nonAutowiredClassNamespaces = array_map(
            static fn (string $className): string => self::GENERATED_CLASS_NAMESPACE.$className,
            $nonAutowiredClassNames,
        );

        self::assertEquals($autowiredClassNamespaces, $config->getAutowiredClasses());

        self::assertEquals($nonAutowiredClassNamespaces, $config->getNonAutowiredClasses());

        $interfaceName = array_keys($configVars['interface_bindings'])[0];
        $interfaceImplementationName = array_values($configVars['interface_bindings'])[0];
        $this->assertEquals(
            $interfaceImplementationName,
            $config->getInterfaceImplementation($interfaceName),
        );

        $this->assertEquals(
            [
                'string' => 'string',
                'float'  => '3.14',
            ],
            $config->getClassBoundVariables($this->classWithBuiltInArgumentTypesNamespace),
        );

        $this->assertEquals(
            ['tag_1', 'tag_2'],
            $config->getClassTags($this->classWithBuiltInArgumentTypesNamespace),
        );
    }

    public function testGetConfigHasNoConfigDir(): void
    {
        $config = $this->getConfig();
        unset($config['config_dir']);

        $this->expectException(InvalidConfigNodeTypeException::class);
        $this->expectExceptionMessage('Node "config_dir" must be of "string" type.');

        new Config($config, env: []);
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

        $this->classWithBuiltInArgumentTypesNamespace = self::GENERATED_CLASS_NAMESPACE.$classWithBuiltInArgumentTypesName;
    }

    private function getConfig(
        array $services = [],
        array $interfaceBindings = [],
        array $classBindings = [],
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
            'config_dir'         => __DIR__,
            'services'           => [
                'include' => [
                    self::GENERATED_CLASS_STUB_PATH.$emptyClassName1.'.php',
                    self::GENERATED_CLASS_STUB_PATH.$emptyClassName2.'.php',
                ],
                'exclude' => [
                    self::GENERATED_CLASS_STUB_PATH.$emptyClassName3.'.php',
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

        if ($services) {
            $config['services'] = $services;
        }

        if ($interfaceBindings) {
            $config['interface_bindings'] = $interfaceBindings;
        }

        if ($classBindings) {
            $config['class_bindings'] = $classBindings;
        }

        return $config;
    }
}

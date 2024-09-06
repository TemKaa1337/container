<?php

declare(strict_types=1);

namespace Container;

use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Exception\RequiredMethodCallException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyLines)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class GeneralRequiredTest extends AbstractContainerTestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDoeToMissingRequiredMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(self::GENERATED_CLASS_NAMESPACE.$className1, requiredMethodCalls: ['create'],
                ),
            ],
        );

        $this->expectException(RequiredMethodCallException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Class "%s" does not have method called "create".',
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToIncorrectDecoratorSignature(): void
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
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                            'inner',
                        ),
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1, %s string $arg2): void
                            {
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(APP_BOUND_VAR)'),
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                            'signature',
                        ),
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
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
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            2,
                            'signature',
                        ),
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
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
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
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
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not resolve decorated class in class "%s" as it does not have argument named "inner".',
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToIncorrectDecoratorSignatureInMultipleMethods(): void
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
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                            'inner',
                        ),
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
                    ->setBody([
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1): void
                            {}
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg2(%s string $arg1, %s $inner): void
                            {}
                            METHOD,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(APP_BOUND_VAR)'),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg3(%s string $arg1, %s int $arg2, %s $inner): void
                            {}
                            METHOD,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(APP_BOUND_VAR)'),
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_FLOAT_VAR)'),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg4(%s string $arg1, %s int $arg2, %s $decorator): void
                            {}
                            METHOD,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(APP_BOUND_VAR)'),
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_FLOAT_VAR)'),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                            'signature',
                        ),
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
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
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            2,
                            'signature',
                        ),
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
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
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
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
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not resolve decorated class in class "%s" as it does not have argument named "inner".',
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToNonExistingVariableBind(): void
    {
        $className1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        'public readonly string $arg1;',
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        <<<'METHOD'
                            public function setArg1(string $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                        METHOD,
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "arg1::string".',
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithRequiredConstructMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        sprintf('public %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        <<<'METHOD'
                            public function __construct()
                            {
                            
                            }
                        METHOD,
                    ]),
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
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(RequiredMethodCallException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not call method "%s::__construct" as it is constructor.',
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );
        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithRequiredPrivateMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        sprintf('public static %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        <<<'METHOD'
                            public function setArg2(): void
                            {
                            
                            }
                        METHOD,
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            private function setArg1(%s $arg1): void
                            {
                                self::$arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                    ]),
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
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(RequiredMethodCallException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not call method "%s::setArg1" as it is not public.',
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );
        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithRequiredProtectedMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        sprintf('public static %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        <<<'METHOD'
                            public function setArg2(): void
                            {
                            
                            }
                        METHOD,
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            protected function setArg1(%s $arg1): void
                            {
                                self::$arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                    ]),
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
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(RequiredMethodCallException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not call method "%s::setArg1" as it is not public.',
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );
        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithRequiredStaticMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        sprintf('public static %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        <<<'METHOD'
                            public function setArg2(): void
                            {
                            
                            }
                        METHOD,
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public static function setArg1(%s $arg1): void
                            {
                                self::$arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                    ]),
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
        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(RequiredMethodCallException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Calling static method "%s::setArg1" is not supported.',
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );
        (new ContainerBuilder())->add($config)->build();
    }
}

<?php

declare(strict_types=1);

namespace Container;

use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Exception\RequiredMethodCallException;
use Temkaa\Container\Exception\UnresolvableArgumentException;
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
                $this->generateClassConfig(
                    self::GENERATED_CLASS_NAMESPACE.$className1,
                    requiredMethodCalls: ['create'],
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
    public function testDoesNotCompileDueToConstructorRequiredCall(): void
    {
        $className1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        'public readonly string $arg1;',
                        <<<'METHOD'
                            public function setArg2(string $arg1 = 'test'): void
                            {

                            }
                        METHOD,
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        <<<'METHOD'
                            public function __construct()
                            {
                            }
                        METHOD,
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
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
                            public function setArg2(string $arg1 = 'test'): void
                            {

                            }
                        METHOD,
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

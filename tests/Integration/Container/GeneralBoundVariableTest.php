<?php

declare(strict_types=1);

namespace Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Exception\Config\EnvVariableCircularException;
use Temkaa\Container\Exception\UnresolvableArgumentException;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

/**
 * @psalm-suppress all
 * @SuppressWarnings(PHPMD)
 */
final class GeneralBoundVariableTest extends AbstractContainerTestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithConfigPrecedence(): void
    {
        $className1 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'attribute_string'),
                        'public readonly string $arg,',
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            ],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    variableBindings: ['$arg' => 'config_string'],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        self::assertSame('config_string', $class1->arg);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToDifferentEnumFromAttribute(): void
    {
        $className = ClassGenerator::getClassName();
        $unitEnum1 = ClassGenerator::getClassName().'UnitEnum';
        $unitEnum2 = ClassGenerator::getClassName().'UnitEnum';
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$unitEnum1.php")
                    ->setName($unitEnum1)
                    ->setPrefix('enum')
                    ->setBody([
                        'case EnumCaseOne;',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$unitEnum2.php")
                    ->setName($unitEnum2)
                    ->setPrefix('enum')
                    ->setBody([
                        'case EnumCaseOne;',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_PARAMETER_RAW_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$unitEnum1.'::EnumCaseOne',
                        ),
                        sprintf(
                            'public readonly %s $enum,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$unitEnum2,
                        ),
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$unitEnum2.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$unitEnum1.'.php',
            ],
        );

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "%s::%s" as bound expression has incompatible type "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
                'enum',
                self::GENERATED_CLASS_NAMESPACE.$unitEnum2,
                self::GENERATED_CLASS_NAMESPACE.$unitEnum1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToDifferentEnumFromConfig(): void
    {
        $className = ClassGenerator::getClassName();
        $unitEnum1 = ClassGenerator::getClassName().'UnitEnum';
        $unitEnum2 = ClassGenerator::getClassName().'UnitEnum';
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$unitEnum1.php")
                    ->setName($unitEnum1)
                    ->setPrefix('enum')
                    ->setBody([
                        'case EnumCaseOne;',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$unitEnum2.php")
                    ->setName($unitEnum2)
                    ->setPrefix('enum')
                    ->setBody([
                        'case EnumCaseOne;',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $enum,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$unitEnum2,
                        ),
                    ]),
            )
            ->generate();

        $enumValue = constant(self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$unitEnum1.'::EnumCaseOne');

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$unitEnum2.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$unitEnum1.'.php',
            ],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: ['$enum' => $enumValue],
                ),
            ],
        );

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "%s::%s" as bound expression has incompatible type "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
                'enum',
                self::GENERATED_CLASS_NAMESPACE.$unitEnum2,
                self::GENERATED_CLASS_NAMESPACE.$unitEnum1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToNonEnumPropertyTypeFromAttribute(): void
    {
        $className = ClassGenerator::getClassName();
        $unitEnum = ClassGenerator::getClassName().'UnitEnum';
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$unitEnum.php")
                    ->setName($unitEnum)
                    ->setPrefix('enum')
                    ->setBody([
                        'case EnumCaseOne;',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_PARAMETER_RAW_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$unitEnum.'::EnumCaseOne',
                        ),
                        'public readonly string $enum,',
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$unitEnum.'.php',
            ],
        );

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "%s::%s" as bound expression has incompatible type "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
                'enum',
                'string',
                self::GENERATED_CLASS_NAMESPACE.$unitEnum,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToNonEnumPropertyTypeFromConfig(): void
    {
        $className = ClassGenerator::getClassName();
        $unitEnum = ClassGenerator::getClassName().'UnitEnum';
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$unitEnum.php")
                    ->setName($unitEnum)
                    ->setPrefix('enum')
                    ->setBody([
                        'case EnumCaseOne;',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $enum,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$unitEnum,
                        ),
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$unitEnum.'.php',
            ],
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className,
                    variableBindings: ['$enum' => 'string'],
                ),
            ],
        );

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with argument "%s::%s" as bound expression has incompatible type "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className,
                'enum',
                self::GENERATED_CLASS_NAMESPACE.$unitEnum,
                'string',
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
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

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];

        $config = $this->generateConfig(includedPaths: $files);

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
    public function testDoesNotCompileWithCircularEnvVariableDependenciesFromAttribute(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(CIRCULAR_ENV_VARIABLE_1)'),
                        'public readonly string $circular,',
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [__DIR__.self::GENERATED_CLASS_STUB_PATH.$className.'.php'],
        );

        $this->expectException(EnvVariableCircularException::class);
        $this->expectExceptionMessage(
            'Cannot resolve env variable "env(CIRCULAR_ENV_VARIABLE_2)" as '
            .'it has circular references "CIRCULAR_ENV_VARIABLE_1 -> CIRCULAR_ENV_VARIABLE_2".',
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithClassCircularEnvVariableDependenciesFromConfig(): void
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

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileWithGlobalCircularEnvVariableDependenciesFromConfig(): void
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

        (new ContainerBuilder())->add($config)->build();
    }
}

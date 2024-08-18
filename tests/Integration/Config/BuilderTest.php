<?php

declare(strict_types=1);

namespace Tests\Integration\Config;

use Temkaa\SimpleContainer\Builder\Config\ClassBuilder as ClassConfigBuilder;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Container\Builder;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\EnvVariableNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\InvalidPathException;
use Temkaa\SimpleContainer\Model\Config\Decorator;
use Temkaa\SimpleContainer\Model\Container\ClassConfig;
use Temkaa\SimpleContainer\Model\Container\ConfigNew;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\AbstractUnitTestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class BuilderTest extends AbstractUnitTestCase
{
    protected const GENERATED_CLASS_STUB_PATH = '/../../Fixture/Stub/Class/';

    public function testConfigDoesNotInitDueToInvalidServicePath(): void
    {
        $config = $this->generateConfig(includedPaths: ['path']);

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('The specified path "path" does not exist.');

        (new Builder())->add($config);
    }

    public function testConfigDoesNotLoadDueToMissingDecorator(): void
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
                            'public readonly %s $dependency',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className1,
                        ),
                    ]),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php"];
        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    decorates: new Decorator(
                        id: 'non_existing_class',
                        signature: '$dependency',
                    ),
                ),
            ],
        );

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage('Class "non_existing_class" is not found.');

        (new Builder())->add($config);
    }

    public function testConfigDoesNotLoadDueToMissingInterface(): void
    {
        $config = $this->generateConfig(interfaceBindings: ['non_existent_interface' => 'class']);

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage('Class "non_existent_interface" is not found.');

        (new Builder())->add($config);
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

        (new Builder())->add($config);
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

        $builder = (new Builder())->add($config);
        self::assertNotNull($builder);

        self::assertEquals(
            ['string' => 'env(APP_BOUND_VAR)'],
            $config->getBoundedClasses()[$classFullNamespace]->getBoundVariables(),
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

        (new Builder())->add($config);
    }

    private function generateClassConfig(
        string $className,
        array $variableBindings = [],
        ?Decorator $decorates = null,
    ): ClassConfig {
        $builder = ClassConfigBuilder::make($className);

        foreach ($variableBindings as $variableName => $variableValue) {
            $builder->bindVariable($variableName, $variableValue);
        }

        if ($decorates) {
            $builder->decorates($decorates->getId(), $decorates->getPriority(), $decorates->getSignature());
        }

        return $builder->build();
    }

    private function generateConfig(
        array $includedPaths = [],
        array $interfaceBindings = [],
        array $classBindings = [],
    ): ConfigNew {
        $builder = new ConfigBuilder();

        if ($includedPaths) {
            foreach ($includedPaths as $path) {
                $builder->include($path);
            }
        }

        if ($interfaceBindings) {
            foreach ($interfaceBindings as $interface => $class) {
                $builder->bindInterface($interface, $class);
            }
        }

        if ($classBindings) {
            foreach ($classBindings as $classBinding) {
                $builder->bindClass($classBinding);
            }
        }

        return $builder->build();
    }
}

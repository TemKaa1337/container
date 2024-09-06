<?php

declare(strict_types=1);

namespace Tests\Integration;

use DirectoryIterator;
use PHPUnit\Framework\TestCase;
use Temkaa\SimpleContainer\Builder\Config\Class\FactoryBuilder;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder as ClassConfigBuilder;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Config\ClassConfig;
use Temkaa\SimpleContainer\Model\Config\Decorator;
use Temkaa\SimpleContainer\Model\Config\Factory;
use Temkaa\SimpleContainer\Util\Flag;

/**
 * @psalm-suppress MixedAssignment, MixedArgumentTypeCoercion, MixedArgument, InternalClass, InternalMethod
 */
abstract class AbstractTestCase extends TestCase
{
    protected const string ATTRIBUTE_ALIAS_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Alias(name: \'%s\')]';
    protected const string ATTRIBUTE_AUTOWIRE_DEFAULT_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Autowire]';
    protected const string ATTRIBUTE_AUTOWIRE_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Autowire(load: %s, singleton: %s)]';
    protected const string ATTRIBUTE_DECORATES_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Decorates(id: %s, priority: %s, signature: \'%s\')]';
    protected const string ATTRIBUTE_FACTORY_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Factory(id: \'%s\', method: \'%s\')]';
    protected const string ATTRIBUTE_INSTANCE_OF_ITERATOR_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Bind\InstanceOfIterator(id: %s)]';
    protected const string ATTRIBUTE_PARAMETER_RAW_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Bind\Parameter(expression: %s)]';
    protected const string ATTRIBUTE_PARAMETER_STRING_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Bind\Parameter(expression: \'%s\')]';
    protected const string ATTRIBUTE_REQUIRED_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Bind\Required()]';
    protected const string ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Bind\TaggedIterator(tag: \'%s\')]';
    protected const string ATTRIBUTE_TAG_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Tag(name: \'%s\')]';
    protected const string GENERATED_CLASS_ABSOLUTE_NAMESPACE = '\Tests\Fixture\Stub\Class\\';
    protected const string GENERATED_CLASS_NAMESPACE = 'Tests\Fixture\Stub\Class\\';
    protected const string GENERATED_CLASS_STUB_PATH = '/../Fixture/Stub/Class/';
    protected const string GITKEEP_FILENAME = '.gitkeep';

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::clearClassFixtures();
    }

    protected static function clearClassFixtures(): void
    {
        $path = realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH);

        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot() || $file->isDir() || $file->getFilename() === self::GITKEEP_FILENAME) {
                continue;
            }

            unlink($file->getRealPath());
        }
    }

    /**
     * @param class-string $className
     */
    protected function generateClassConfig(
        string $className,
        array $variableBindings = [],
        array $aliases = [],
        ?Decorator $decorates = null,
        bool $singleton = true,
        ?Factory $factory = null,
        array $tags = [],
        array $requiredMethodCalls = [],
    ): ClassConfig {
        $builder = ClassConfigBuilder::make($className);

        foreach ($variableBindings as $variableName => $variableValue) {
            $builder->bindVariable($variableName, $variableValue);
        }

        foreach ($aliases as $alias) {
            $builder->alias($alias);
        }

        if ($decorates) {
            /** @psalm-suppress InternalMethod */
            $builder->decorates($decorates->getId(), $decorates->getPriority(), $decorates->getSignature());
        }

        foreach ($tags as $tag) {
            $builder->tag($tag);
        }

        if ($factory) {
            $factoryBuilder = FactoryBuilder::make($factory->getId(), $factory->getMethod());

            foreach ($factory->getBoundedVariables() as $variableName => $variableValue) {
                $factoryBuilder->bindVariable($variableName, $variableValue);
            }

            $builder->factory($factoryBuilder->build());
        }

        foreach ($requiredMethodCalls as $method) {
            $builder->call($method);
        }

        if ($singleton) {
            $builder->singleton();
        } else {
            $builder->singleton($singleton);
        }

        return $builder->build();
    }

    protected function generateConfig(
        array $includedPaths = [],
        array $excludedPaths = [],
        array $globalBoundVariables = [],
        array $interfaceBindings = [],
        array $classBindings = [],
    ): Config {
        $builder = new ConfigBuilder();

        foreach ($includedPaths as $path) {
            $builder->include($path);
        }

        foreach ($excludedPaths as $path) {
            $builder->exclude($path);
        }

        foreach ($globalBoundVariables as $variableName => $variableValue) {
            $builder->bindVariable($variableName, $variableValue);
        }

        foreach ($interfaceBindings as $interface => $class) {
            $builder->bindInterface($interface, $class);
        }

        foreach ($classBindings as $classBinding) {
            $builder->bindClass($classBinding);
        }

        return $builder->build();
    }

    protected function setUp(): void
    {
        parent::setUp();

        Flag::clear();
    }
}

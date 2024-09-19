<?php

declare(strict_types=1);

namespace Tests\Integration;

use DirectoryIterator;
use PHPUnit\Framework\TestCase;
use Temkaa\Container\Builder\Config\Class\FactoryBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder as ClassConfigBuilder;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Config\ClassConfig;
use Temkaa\Container\Model\Config\Decorator;
use Temkaa\Container\Model\Config\Factory;
use Temkaa\Container\Util\Flag;

/**
 * @psalm-suppress MixedAssignment, MixedArgumentTypeCoercion, MixedArgument, InternalClass, InternalMethod
 */
abstract class AbstractTestCase extends TestCase
{
    protected const string ATTRIBUTE_ALIAS_SIGNATURE = '#[\Temkaa\Container\Attribute\Alias(name: \'%s\')]';
    protected const string ATTRIBUTE_AUTOWIRE_DEFAULT_SIGNATURE = '#[\Temkaa\Container\Attribute\Autowire]';
    protected const string ATTRIBUTE_AUTOWIRE_SIGNATURE = '#[\Temkaa\Container\Attribute\Autowire(load: %s, singleton: %s)]';
    protected const string ATTRIBUTE_DECORATES_SIGNATURE = '#[\Temkaa\Container\Attribute\Decorates(id: %s, priority: %s)]';
    protected const string ATTRIBUTE_FACTORY_SIGNATURE = '#[\Temkaa\Container\Attribute\Factory(id: \'%s\', method: \'%s\')]';
    protected const string ATTRIBUTE_INSTANCE_OF_ITERATOR_SIGNATURE = '#[\Temkaa\Container\Attribute\Bind\InstanceOfIterator(id: %s)]';
    protected const string ATTRIBUTE_PARAMETER_RAW_SIGNATURE = '#[\Temkaa\Container\Attribute\Bind\Parameter(expression: %s)]';
    protected const string ATTRIBUTE_PARAMETER_STRING_SIGNATURE = '#[\Temkaa\Container\Attribute\Bind\Parameter(expression: \'%s\')]';
    protected const string ATTRIBUTE_REQUIRED_SIGNATURE = '#[\Temkaa\Container\Attribute\Bind\Required()]';
    protected const string ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE = '#[\Temkaa\Container\Attribute\Bind\TaggedIterator(tag: \'%s\')]';
    protected const string ATTRIBUTE_TAG_SIGNATURE = '#[\Temkaa\Container\Attribute\Tag(name: \'%s\')]';
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
            $builder->decorates($decorates->getId(), $decorates->getPriority());
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

<?php

declare(strict_types=1);

namespace Tests\Integration;

use DirectoryIterator;
use PHPUnit\Framework\TestCase;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder as ClassConfigBuilder;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Config\ClassConfig;
use Temkaa\SimpleContainer\Model\Config\Decorator;
use Temkaa\SimpleContainer\Util\Flag;

/**
 * @psalm-suppress MixedAssignment, MixedArgumentTypeCoercion, MixedArgument, InternalClass, InternalMethod
 */
abstract class AbstractTestCase extends TestCase
{
    protected const string ATTRIBUTE_ALIAS_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Alias(name: \'%s\')]';
    protected const string ATTRIBUTE_AUTOWIRE_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Autowire(load: %s, singleton: %s)]';
    protected const string ATTRIBUTE_DECORATES_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Decorates(id: %s, priority: %s, signature: \'%s\')]';
    protected const string ATTRIBUTE_PARAMETER_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Bind\Parameter(expression: \'%s\')]';
    protected const string ATTRIBUTE_TAGGED_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Bind\Tagged(tag: \'%s\')]';
    protected const string ATTRIBUTE_TAG_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Tag(name: \'%s\')]';
    protected const string GENERATED_CLASS_ABSOLUTE_NAMESPACE = '\Tests\Fixture\Stub\Class\\';
    protected const string GENERATED_CLASS_NAMESPACE = 'Tests\Fixture\Stub\Class\\';
    protected const string GENERATED_CLASS_STUB_PATH = '/../Fixture/Stub/Class/';
    protected const string GITKEEP_FILENAME = '.gitkeep';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $envVariables = [
            'APP_BOUND_VAR'           => 'bound_variable_value',
            'ENV_CASTABLE_STRING_VAR' => '10.1',
            'ENV_FLOAT_VAR'           => '10.1',
            'ENV_BOOL_VAL'            => 'false',
            'ENV_INT_VAL'             => '3',
            'ENV_STRING_VAL'          => 'string',
            'ENV_STRING_VAR'          => 'string',
            'ENV_VAR_1'               => 'test_one',
            'ENV_VAR_2'               => '10.1',
            'ENV_VAR_3'               => 'test-three',
            'ENV_VAR_4'               => 'true',
            'CIRCULAR_ENV_VARIABLE_1' => 'env(CIRCULAR_ENV_VARIABLE_2)',
            'CIRCULAR_ENV_VARIABLE_2' => 'env(CIRCULAR_ENV_VARIABLE_1)',
            'ENV_VARIABLE_REFERENCE'  => 'env(ENV_STRING_VAR)_additional_string',
        ];

        foreach ($envVariables as $name => $value) {
            putenv("$name=$value");
        }
    }

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
        array $tags = [],
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

        $builder->singleton($singleton);

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

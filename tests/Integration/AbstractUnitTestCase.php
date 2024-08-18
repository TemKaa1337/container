<?php

declare(strict_types=1);

namespace Tests\Integration;

use DirectoryIterator;
use PHPUnit\Framework\TestCase;

abstract class AbstractUnitTestCase extends TestCase
{
    protected const ATTRIBUTE_ALIAS_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Alias(name: \'%s\')]';
    protected const ATTRIBUTE_AUTOWIRE_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Autowire(load: %s, singleton: %s)]';
    protected const ATTRIBUTE_DECORATES_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Decorates(id: %s, priority: %s, signature: \'%s\')]';
    protected const ATTRIBUTE_PARAMETER_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Bind\Parameter(expression: \'%s\')]';
    protected const ATTRIBUTE_TAGGED_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Bind\Tagged(tag: \'%s\')]';
    protected const ATTRIBUTE_TAG_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Tag(name: \'%s\')]';
    protected const GENERATED_CLASS_ABSOLUTE_NAMESPACE = '\Tests\Fixture\Stub\Class\\';
    protected const GENERATED_CLASS_CONFIG_RELATIVE_PATH = '/../Class/';
    protected const GENERATED_CLASS_NAMESPACE = 'Tests\Fixture\Stub\Class\\';
    protected const GENERATED_CLASS_STUB_PATH = '/../Fixture/Stub/Class/';
    protected const GITKEEP_FILENAME = '.gitkeep';

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $path = realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH);

        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot() || $file->isDir() || $file->getFilename() === self::GITKEEP_FILENAME) {
                continue;
            }

            unlink($file->getRealPath());
        }
    }

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
}

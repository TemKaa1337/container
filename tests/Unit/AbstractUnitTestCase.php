<?php

declare(strict_types=1);

namespace Tests\Unit;

use DirectoryIterator;
use PHPUnit\Framework\TestCase;

abstract class AbstractUnitTestCase extends TestCase
{
    protected const ATTRIBUTE_ALIAS_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Alias(name: \'%s\')]';
    protected const ATTRIBUTE_AUTOWIRE_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Autowire(load: %s, singleton: %s)]';
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
}

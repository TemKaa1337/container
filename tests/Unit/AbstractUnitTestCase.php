<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Helper\ClassStubGeneratorTrait;

abstract class AbstractUnitTestCase extends TestCase
{
    use ClassStubGeneratorTrait;

    protected const ATTRIBUTE_ALIAS_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Alias(name: \'%s\')]';
    protected const ATTRIBUTE_NON_AUTOWIRABLE_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\NonAutowirable]';
    protected const ATTRIBUTE_PARAMETER_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Bind\Parameter(expression: \'%s\')]';
    protected const ATTRIBUTE_TAGGED_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Bind\Tagged(tag: \'%s\')]';
    protected const ATTRIBUTE_TAG_SIGNATURE = '#[\Temkaa\SimpleContainer\Attribute\Tag(name: \'%s\')]';
    protected const GENERATED_CLASS_ABSOLUTE_NAMESPACE = '\Tests\Fixture\Stub\Class\\';
    protected const GENERATED_CLASS_NAMESPACE = 'Tests\Fixture\Stub\Class\\';
    protected const GENERATED_CLASS_STUB_PATH = '/../Fixture/Stub/Class/';

    private static int $generatedClassNumber = 0;

    protected static function getNextGeneratedClassNumber(): int
    {
        return ++self::$generatedClassNumber;
    }
}

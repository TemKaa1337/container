<?php

declare(strict_types=1);

namespace Tests\Fixture\Stub;

enum StringBackedEnum: string
{
    case NumericCase = '10.5';
    case TestCase = 'TestCase';
}

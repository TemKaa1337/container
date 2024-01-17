<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class NonAutowirable
{
}

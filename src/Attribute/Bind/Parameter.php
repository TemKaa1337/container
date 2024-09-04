<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Attribute\Bind;

use Attribute;
use UnitEnum;

/**
 * @psalm-api
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_METHOD)]
final readonly class Parameter
{
    public function __construct(
        public string|UnitEnum $expression,
    ) {
    }
}

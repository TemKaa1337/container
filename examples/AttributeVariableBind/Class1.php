<?php

declare(strict_types=1);

namespace Example\AttributeVariableBind;

use Temkaa\Container\Attribute\Bind\Parameter;

final readonly class Class1
{
    public function __construct(
        #[Parameter('variable_1_value')]
        public string $variable1,
        #[Parameter('env(ENV_VAR_2)')]
        public int $variable2,
        #[Parameter(Enum1::EnumCase)]
        public Enum1 $variable3,
    ) {
    }
}

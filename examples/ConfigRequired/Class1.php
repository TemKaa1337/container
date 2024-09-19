<?php

declare(strict_types=1);

namespace Example\ConfigRequired;

use Temkaa\Container\Attribute\Bind\Required;

final readonly class Class1
{
    private Class2 $class2;

    #[Required]
    public function setClass(Class2 $class2): void
    {
        $this->class2 = $class2;
    }
}

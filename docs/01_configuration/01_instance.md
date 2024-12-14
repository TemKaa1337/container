# Using instances

This package provides option to bind specific class from container into class.

Example using config:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Attribute\Bind\Instance;

interface SomeInterface
{
}

final readonly class ClassImplementing1 implements SomeInterface
{
}

final readonly class ClassImplementing2 implements SomeInterface
{
}

final readonly class Collector
{
    public function __construct(
        private object $class,
    ) {
    }
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->configure(
        ClassBuilder::make(Collector::class)
            // Here you say: please bind me this particular class into this property and it doesn't matter what type
            // is hinted in that property 
            ->bindVariable('$class', new Instance(id: ClassImplementing1::class))
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$class = $container->get(Collector::class);
assert($class->class instanceof ClassImplementing1);
```
The same using attributes:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Attribute\Bind\Instance;

interface SomeInterface
{
}

final readonly class ClassImplementing1 implements SomeInterface
{
}

final readonly class ClassImplementing2 implements SomeInterface
{
}

final readonly class Collector
{
    public function __construct(
        #[Instance(id: ClassImplementing1::class)]
        private object $class,
    ) {
    }
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$class = $container->get(Collector::class);
assert($class->class instanceof ClassImplementing1);
```

# Binding interfaces to specific implementations

Using this feature you can bind any interface to specific implementation and any class which uses this interface will
be provided with its implementation. This is very useful when face the situation where you want to swap implementation.
Example (this works only with config, no attribute is provided for this feature):
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

interface SomeInterface
{
}

final readonly class SomeClass1 implements SomeInterface
{
}

final readonly class SomeClass2 implements SomeInterface
{
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->bindInterface(SomeInterface::class, SomeClass2::class)
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

// instance of SomeClass2
$class = $container->get(SomeInterface::class);
```
One note here - this package can auto discover interface implementations, lets say you have an interface and only 1 class
implements this interface. In this case you don't need to explicitly specify its implementation, container will 
automatically discover it:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

interface SomeInterface
{
}

final readonly class SomeClassImplementingInterface implements SomeInterface
{
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

// container auto-discovered interface implementation, and you don't need to explicitly bind implementation 
$interfaceImplementation = $container->get(SomeInterface::class);
```

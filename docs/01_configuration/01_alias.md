# Using aliases

This package allows you to add aliases to needed classes and then retrieve them by this alias.
Example using config:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;

final readonly class ClassWithAlias
{
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->bindClass(
        ClassBuilder::make(ClassWithAlias::class)
            ->alias('alias_1')
            ->alias('alias_2')
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

// now yoy can retrieve `ClassWithAlias` using its direct classname or with alias:
$classWithAlias = $container->get(ClassWithAlias::class);
// or
$classWithAlias = $container->get('alias_1');
$classWithAlias = $container->get('alias_2');
```

Example using attributes:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Alias;

#[Alias('alias_1')]
#[Alias('alias_2')]
final readonly class ClassWithAlias
{
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$classWithAlias = $container->get(ClassWithAlias::class);
// or
$classWithAlias = $container->get('alias_1');
$classWithAlias = $container->get('alias_2');
```

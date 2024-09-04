# Container self reference

If you appeared in situation when you need directly access in some classes to container, you can type-hint it and 
container will inject itself into you class constructor, you don't need to configure anything!
```php
<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Container;

class ClassWithInjectedConstructor
{
    public function __construct(
        // I suggest you to inject interfaces everywhere you can, rather than classes
        private readonly ContainerInterface $containerByInterface,
        // or
        private readonly Container $containerByClass,
    ) {
    }
}

$container = ConfigBuilder::make()->include('add path of your class here')->build();

$class = $container->get(ClassWithInjectedConstructor::class);
```

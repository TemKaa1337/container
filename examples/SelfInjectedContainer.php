<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\SelfInjectedContainer\Class1;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/SelfInjectedContainer/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\SelfInjectedContainer\Class1)#17 (2) {
 *     ["containerFromClass"]=>
 *     object(Temkaa\SimpleContainer\Container) (3) {...}
 *     ["containerFromInterface"]=>
 *     object(Psr\Container\ContainerInterface) (4) {...}
 * }
 */
$class1 = $container->get(Class1::class);

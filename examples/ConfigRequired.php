<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\ConfigRequired\Class1;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/ConfigRequired/')
    ->bindClass(
        ClassBuilder::make(Class1::class)
            ->call('setClass')
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\ConfigRequired\Class1)#33 (1) {
 *     ["class2":"Example\ConfigRequired\Class1":private]=>
 *     object(Example\ConfigRequired\Class2)#32 (0) {
 *     }
 * }
 */
$class = $container->get(Class1::class);

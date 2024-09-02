<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\AttributeRequired\Class1;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/AttributeRequired/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\AttributeRequired\Class1)#32 (1) {
 *     ["class2":"Example\AttributeRequired\Class1":private]=>
 *     object(Example\AttributeRequired\Class2)#31 (0) {
 *     }
 * }
 */
$class = $container->get(Class1::class);

<?php

declare(strict_types=1);

namespace Example;

use Example\ConfigInstance\Class2;
use Example\ConfigInstance\Collector;
use Temkaa\Container\Attribute\Bind\Instance;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;

require __DIR__.'/../vendor/autoload.php';

$config = ConfigBuilder::make()
    ->include(__DIR__.'/ConfigInstance/')
    ->bindClass(
        ClassBuilder::make(Collector::class)
            ->bindVariable('object', new Instance(id: Class2::class))
            ->build()
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\ConfigInstance\Collector)#42 (1) {
 *     ["object"]=>
 *     object(Example\ConfigInstance\Class2)#41 (0) {
 *     }
 * }
 */
$class = $container->get(Collector::class);

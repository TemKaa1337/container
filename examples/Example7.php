<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\Example7\Class1;
use Example\Example7\Class2;
use Example\Example7\Collector;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/Example7/')
    ->bindClass(
        ClassBuilder::make(Class1::class)
            ->tag('tag')
            ->build(),
    )
    ->bindClass(
        ClassBuilder::make(Class2::class)
            ->tag('tag')
            ->build(),
    )
    ->bindClass(
        ClassBuilder::make(Collector::class)
            ->bindVariable('objects', '!tagged tag')
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\Example7\Collector)#27 (1) {
 *     ["objects"]=>
 *     array(2) {
 *         [0]=>
 *         object(Example\Example7\Class1)#17 (0) {
 *         }
 *         [1]=>
 *         object(Example\Example7\Class2)#23 (0) {
 *         }
 *     }
 * }
 */
$class = $container->get(Collector::class);

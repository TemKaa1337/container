<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\ConfigTaggedIterator\Class1;
use Example\ConfigTaggedIterator\Class2;
use Example\ConfigTaggedIterator\Collector;
use Temkaa\SimpleContainer\Attribute\Bind\TaggedIterator;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/ConfigTaggedIterator/')
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
            ->bindVariable('objects', new TaggedIterator('tag'))
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

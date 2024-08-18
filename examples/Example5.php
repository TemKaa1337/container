<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\Example5\Class1;
use Example\Example5\Class2;
use Example\Example5\Class3;
use Example\Example5\Collector;
use Example\Example5\Interface1;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/Example5/')
    ->bindInterface(Interface1::class, Class1::class)
    ->bindClass(
        ClassBuilder::make(Class2::class)
            ->decorates(id: Interface1::class, priority: 2)
            ->build(),
    )
    ->bindClass(
        ClassBuilder::make(Class3::class)
            ->decorates(id: Interface1::class, priority: 1, signature: 'class')
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\Example5\Class3)#31 (1) {
 *     ["class"]=>
 *     object(Example\Example5\Class2)#28 (1) {
 *         ["inner"]=>
 *         object(Example\Example5\Class1)#29 (0) {
 *         }
 *     }
 * }
 */
$class = $container->get(Interface1::class);

/**
 * object(Example\Example5\Collector)#32 (1) {
 *     ["class"]=>
 *     object(Example\Example5\Class3)#31 (1) {
 *         ["class"]=>
 *         object(Example\Example5\Class2)#28 (1) {
 *             ["inner"]=>
 *             object(Example\Example5\Class1)#29 (0) {
 *             }
 *         }
 *     }
 * }
 */
$class = $container->get(Collector::class);

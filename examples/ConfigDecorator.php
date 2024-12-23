<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\ConfigDecorator\Class1;
use Example\ConfigDecorator\Class2;
use Example\ConfigDecorator\Class3;
use Example\ConfigDecorator\Collector;
use Example\ConfigDecorator\Interface1;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/ConfigDecorator/')
    ->bindInterface(Interface1::class, Class1::class)
    ->configure(
        ClassBuilder::make(Class2::class)
            ->decorates(id: Interface1::class, priority: 2)
            ->build(),
    )
    ->configure(
        ClassBuilder::make(Class3::class)
            ->decorates(id: Interface1::class, priority: 1)
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

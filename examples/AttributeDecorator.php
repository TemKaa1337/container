<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\AttributeDecorator\Class1;
use Example\AttributeDecorator\Collector;
use Example\AttributeDecorator\Interface1;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/AttributeDecorator/')
    ->bindInterface(Interface1::class, Class1::class)
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
 * object(Example\Example6\Collector)#29 (1) {
 *     ["class"]=>
 *     object(Example\Example6\Class3)#26 (1) {
 *         ["class"]=>
 *         object(Example\Example6\Class2)#27 (1) {
 *         ["inner"]=>
 *             object(Example\Example6\Class1)#14 (0) {
 *             }
 *         }
 *     }
 * }
 */
$class = $container->get(Collector::class);

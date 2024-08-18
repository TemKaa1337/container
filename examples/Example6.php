<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\Example6\Class1;
use Example\Example6\Collector;
use Example\Example6\Interface1;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/Example6/')
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

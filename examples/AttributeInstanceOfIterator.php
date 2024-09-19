<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\AttributeInstanceOfIterator\Class4;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/AttributeInstanceOfIterator/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\AttributeInstanceOfIterator\Class4)#38 (1) {
 *     ["classes":"Example\AttributeInstanceOfIterator\Class4":private]=>
 *     array(2) {
 *         [0]=>
 *         object(Example\AttributeInstanceOfIterator\Class1)#33 (0) {
 *         }
 *         [1]=>
 *         object(Example\AttributeInstanceOfIterator\Class2)#37 (0) {
 *         }
 *     }
 * }
 */
$class = $container->get(Class4::class);

<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\AttributeTaggedIterator\Collector;
use Example\AttributeTaggedIterator\InterfaceCollector;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/AttributeTaggedIterator/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\AttributeTaggedIterator\Collector)#53 (4) {
 *     ["list"]=>
 *     array(2) {
 *         [0]=>
 *         object(Example\AttributeTaggedIterator\Class1)#39 (0) {
 *         }
 *         [1]=>
 *         object(Example\AttributeTaggedIterator\Class2)#40 (0) {
 *         }
 *     }
 *     ["arrayWithClassNamespaceKey"]=>
 *     array(2) {
 *         ["Example\AttributeTaggedIterator\Class1"]=>
 *         object(Example\AttributeTaggedIterator\Class1)#39 (0) {
 *         }
 *         ["Example\AttributeTaggedIterator\Class2"]=>
 *         object(Example\AttributeTaggedIterator\Class2)#40 (0) {
 *         }
 *     }
 *     ["arrayWithClassNameKey"]=>
 *     array(2) {
 *         ["Class1"]=>
 *         object(Example\AttributeTaggedIterator\Class1)#39 (0) {
 *         }
 *         ["Class2"]=>
 *         object(Example\AttributeTaggedIterator\Class2)#40 (0) {
 *         }
 *     }
 *     ["arrayWithCustomKey"]=>
 *     array(2) {
 *         ["first_class"]=>
 *         object(Example\AttributeTaggedIterator\Class1)#39 (0) {
 *         }
 *         ["second_class"]=>
 *         object(Example\AttributeTaggedIterator\Class2)#40 (0) {
 *         }
 *     }
 * }
 */
$class = $container->get(Collector::class);

/**
 * object(Example\AttributeTaggedIterator\InterfaceCollector)#55 (4) {
 *     ["list"]=>
 *     array(2) {
 *         [0]=>
 *         object(Example\AttributeTaggedIterator\Class3)#54 (0) {
 *         }
 *         [1]=>
 *         object(Example\AttributeTaggedIterator\Class4)#46 (0) {
 *         }
 *     }
 *     ["arrayWithClassNamespaceKey"]=>
 *     array(2) {
 *         ["Example\AttributeTaggedIterator\Class3"]=>
 *         object(Example\AttributeTaggedIterator\Class3)#54 (0) {
 *         }
 *         ["Example\AttributeTaggedIterator\Class4"]=>
 *         object(Example\AttributeTaggedIterator\Class4)#46 (0) {
 *         }
 *     }
 *     ["arrayWithClassNameKey"]=>
 *     array(2) {
 *         ["Class3"]=>
 *         object(Example\AttributeTaggedIterator\Class3)#54 (0) {
 *         }
 *         ["Class4"]=>
 *         object(Example\AttributeTaggedIterator\Class4)#46 (0) {
 *         }
 *     }
 *     ["arrayWithCustomKey"]=>
 *     array(2) {
 *         ["third_class"]=>
 *         object(Example\AttributeTaggedIterator\Class3)#54 (0) {
 *         }
 *         ["fourth_class"]=>
 *         object(Example\AttributeTaggedIterator\Class4)#46 (0) {
 *         }
 *     }
 * }
 */
$class = $container->get(InterfaceCollector::class);

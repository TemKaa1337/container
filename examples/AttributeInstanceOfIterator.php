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
 * object(Example\AttributeInstanceOfIterator\Class4)#46 (4) {
 *     ["list":"Example\AttributeInstanceOfIterator\Class4":private]=>
 *     array(2) {
 *         [0]=>
 *         object(Example\AttributeInstanceOfIterator\Class1)#35 (0) {
 *         }
 *         [1]=>
 *         object(Example\AttributeInstanceOfIterator\Class2)#37 (0) {
 *         }
 *     }
 *     ["arrayWithClassNamespaceKey":"Example\AttributeInstanceOfIterator\Class4":private]=>
 *     array(2) {
 *         ["Example\AttributeInstanceOfIterator\Class1"]=>
 *         object(Example\AttributeInstanceOfIterator\Class1)#35 (0) {
 *         }
 *         ["Example\AttributeInstanceOfIterator\Class2"]=>
 *         object(Example\AttributeInstanceOfIterator\Class2)#37 (0) {
 *         }
 *     }
 *     ["arrayWithClassNameKey":"Example\AttributeInstanceOfIterator\Class4":private]=>
 *     array(2) {
 *         ["Class1"]=>
 *         object(Example\AttributeInstanceOfIterator\Class1)#35 (0) {
 *         }
 *         ["Class2"]=>
 *         object(Example\AttributeInstanceOfIterator\Class2)#37 (0) {
 *         }
 *     }
 *     ["arrayWithCustomKey":"Example\AttributeInstanceOfIterator\Class4":private]=>
 *     array(2) {
 *         ["first_class"]=>
 *         object(Example\AttributeInstanceOfIterator\Class1)#35 (0) {
 *         }
 *         ["second_class"]=>
 *         object(Example\AttributeInstanceOfIterator\Class2)#37 (0) {
 *         }
 *     }
 * }
 */
$class = $container->get(Class4::class);

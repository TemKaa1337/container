<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\ConfigInstanceOfIterator\Class1;
use Example\ConfigInstanceOfIterator\Class2;
use Example\ConfigInstanceOfIterator\Class3;
use Example\ConfigInstanceOfIterator\Class4;
use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/ConfigInstanceOfIterator/')
    ->bindClass(
        ClassBuilder::make(Class4::class)
            ->bindVariable('$list', new InstanceOfIterator(Class3::class))
            ->bindVariable(
                '$arrayWithNamespaceKey',
                new InstanceOfIterator(Class3::class, format: IteratorFormat::ArrayWithClassNamespaceKey),
            )
            ->bindVariable(
                '$arrayWithClassNameKey',
                new InstanceOfIterator(Class3::class, format: IteratorFormat::ArrayWithClassNameKey),
            )
            ->bindVariable(
                '$arrayWithCustomKey',
                new InstanceOfIterator(
                    Class3::class,
                    format: IteratorFormat::ArrayWithCustomKey,
                    customFormatMapping: [
                        Class1::class => 'first_class',
                        Class2::class => 'second_class',
                    ],
                ),
            )
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\ConfigInstanceOfIterator\Class4)#45 (4) {
 *     ["list":"Example\ConfigInstanceOfIterator\Class4":private]=>
 *     array(2) {
 *         [0]=>
 *         object(Example\ConfigInstanceOfIterator\Class1)#44 (0) {
 *         }
 *         [1]=>
 *         object(Example\ConfigInstanceOfIterator\Class2)#46 (0) {
 *         }
 *     }
 *     ["arrayWithNamespaceKey":"Example\ConfigInstanceOfIterator\Class4":private]=>
 *     array(2) {
 *         ["Example\ConfigInstanceOfIterator\Class1"]=>
 *         object(Example\ConfigInstanceOfIterator\Class1)#44 (0) {
 *         }
 *         ["Example\ConfigInstanceOfIterator\Class2"]=>
 *         object(Example\ConfigInstanceOfIterator\Class2)#46 (0) {
 *         }
 *     }
 *     ["arrayWithClassNameKey":"Example\ConfigInstanceOfIterator\Class4":private]=>
 *     array(2) {
 *         ["Class1"]=>
 *         object(Example\ConfigInstanceOfIterator\Class1)#44 (0) {
 *         }
 *         ["Class2"]=>
 *         object(Example\ConfigInstanceOfIterator\Class2)#46 (0) {
 *         }
 *     }
 *     ["arrayWithCustomKey":"Example\ConfigInstanceOfIterator\Class4":private]=>
 *     array(2) {
 *         ["first_class"]=>
 *         object(Example\ConfigInstanceOfIterator\Class1)#44 (0) {
 *         }
 *         ["second_class"]=>
 *         object(Example\ConfigInstanceOfIterator\Class2)#46 (0) {
 *         }
 *     }
 * }
 */
$class = $container->get(Class4::class);

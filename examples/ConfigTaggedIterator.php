<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\ConfigTaggedIterator\Class1;
use Example\ConfigTaggedIterator\Class2;
use Example\ConfigTaggedIterator\Collector;
use Temkaa\Container\Attribute\Bind\TaggedIterator;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;

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
            ->bindVariable('$list', new TaggedIterator('tag'))
            ->bindVariable(
                '$arrayWithNamespaceKey',
                new TaggedIterator('tag', format: IteratorFormat::ArrayWithClassNamespaceKey),
            )
            ->bindVariable(
                '$arrayWithClassNameKey',
                new TaggedIterator('tag', format: IteratorFormat::ArrayWithClassNameKey),
            )
            ->bindVariable(
                '$arrayWithCustomKey',
                new TaggedIterator(
                    'tag',
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
 * object(Example\ConfigTaggedIterator\Collector)#47 (4) {
 *     ["list":"Example\ConfigTaggedIterator\Collector":private]=>
 *     array(2) {
 *          [0]=>
 *          object(Example\ConfigTaggedIterator\Class1)#46 (0) {
 *          }
 *          [1]=>
 *          object(Example\ConfigTaggedIterator\Class2)#48 (0) {
 *          }
 *     }
 *     ["arrayWithNamespaceKey":"Example\ConfigTaggedIterator\Collector":private]=>
 *     array(2) {
 *         ["Example\ConfigTaggedIterator\Class1"]=>
 *         object(Example\ConfigTaggedIterator\Class1)#46 (0) {
 *         }
 *         ["Example\ConfigTaggedIterator\Class2"]=>
 *         object(Example\ConfigTaggedIterator\Class2)#48 (0) {
 *         }
 *     }
 *     ["arrayWithClassNameKey":"Example\ConfigTaggedIterator\Collector":private]=>
 *     array(2) {
 *         ["Class1"]=>
 *         object(Example\ConfigTaggedIterator\Class1)#46 (0) {
 *         }
 *         ["Class2"]=>
 *         object(Example\ConfigTaggedIterator\Class2)#48 (0) {
 *         }
 *     }
 *     ["arrayWithCustomKey":"Example\ConfigTaggedIterator\Collector":private]=>
 *     array(2) {
 *         ["first_class"]=>
 *         object(Example\ConfigTaggedIterator\Class1)#46 (0) {
 *         }
 *         ["second_class"]=>
 *         object(Example\ConfigTaggedIterator\Class2)#48 (0) {
 *         }
 *     }
 * }
 */
$class = $container->get(Collector::class);

<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\ConfigInstanceOfIterator\Class3;
use Example\ConfigInstanceOfIterator\Class4;
use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/ConfigInstanceOfIterator/')
    ->bindClass(
        ClassBuilder::make(Class4::class)
            ->bindVariable('classes', new InstanceOfIterator(Class3::class))
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\ConfigInstanceOfIterator\Class4)#40 (1) {
 *     ["classes":"Example\ConfigInstanceOfIterator\Class4":private]=>
 *     array(2) {
 *         [0]=>
 *         object(Example\ConfigInstanceOfIterator\Class1)#38 (0) {
 *         }
 *         [1]=>
 *         object(Example\ConfigInstanceOfIterator\Class2)#39 (0) {
 *         }
 *     }
 * }
 */
$class = $container->get(Class4::class);

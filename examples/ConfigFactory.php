<?php

declare(strict_types=1);

namespace Example;

use Example\ConfigFactory\Class1;
use Example\ConfigFactory\Class2;
use Temkaa\Container\Attribute\Bind\TaggedIterator;
use Temkaa\Container\Builder\Config\Class\FactoryBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;

require __DIR__.'/../vendor/autoload.php';

$config = ConfigBuilder::make()
    ->include(__DIR__.'/ConfigFactory/')
    ->configure(
        ClassBuilder::make(Class1::class)
            ->factory(
                FactoryBuilder::make(Class2::class, method: 'create')
                    ->bindVariable('tagged', new TaggedIterator('tag'))
                    ->bindVariable('intVar', '1')
                    ->build(),
            )
            ->build(),
    )
    ->configure(
        ClassBuilder::make(Class2::class)
            ->bindVariable('$stringVar', 'string_var')
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\ConfigFactory\Class1)#39 (4) {
 *     ["class3":"Example\ConfigFactory\Class1":private]=>
 *     object(Example\ConfigFactory\Class3)#28 (0) {
 *     }
 *     ["stringVar":"Example\ConfigFactory\Class1":private]=>
 *     string(10) "string_var"
 *     ["intVar":"Example\ConfigFactory\Class1":private]=>
 *     int(1)
 *     ["tagged":"Example\ConfigFactory\Class1":private]=>
 *     array(1) {
 *         [0]=>
 *         object(Example\ConfigFactory\Class4)#42 (0) {
 *         }
 *     }
 * }
 */
$class = $container->get(Class1::class);

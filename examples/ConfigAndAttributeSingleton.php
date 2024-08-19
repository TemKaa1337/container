<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\ConfigAndAttributeSingleton\Class1;
use Example\ConfigAndAttributeSingleton\Class2;
use Example\ConfigAndAttributeSingleton\Class3;
use Example\ConfigAndAttributeSingleton\Class4;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/ConfigAndAttributeSingleton/')
    ->bindClass(
        ClassBuilder::make(Class4::class)
            ->singleton(false)
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\Example10\Class1)#8 (0) {
 * }
 */
$class = $container->get(Class1::class);

/**
 * object(Example\Example10\Class1)#7 (0) {
 * }
 */
$class = $container->get(Class1::class);

/**
 * object(Example\Example10\Class2)#20 (1) {
 *     ["class"]=>
 *     object(Example\Example10\Class1)#22 (0) {
 *     }
 * }
 */
$class = $container->get(Class2::class);

/**
 * object(Example\ConfigAndAttributeSingleton\Class4)#4 (0) {
 * }
 */
$class = $container->get(Class4::class);

/**
 * object(Example\ConfigAndAttributeSingleton\Class4)#10 (0) {
 * }
 */
$class = $container->get(Class4::class);

/**
 * Fatal error: Uncaught Temkaa\SimpleContainer\Exception\EntryNotFoundException: Entry "Could not find entry "Example\Example10\Class3".
 */
$container->get(Class3::class);

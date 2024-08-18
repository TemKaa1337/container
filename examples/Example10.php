<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\Example10\Class1;
use Example\Example10\Class2;
use Example\Example10\Class3;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/Example10/')
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
 * Fatal error: Uncaught Temkaa\SimpleContainer\Exception\EntryNotFoundException: Entry "Could not find entry "Example\Example10\Class3".
 */
$container->get(Class3::class);

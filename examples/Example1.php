<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\Example1\Class1;
use Example\Example1\Class3;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

// TODO: update readme
// TODO: rename example classes
$config = ConfigBuilder::make()
    ->include(__DIR__.'/Example1/')
    ->exclude(__DIR__.'/Example1/Class3.php')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\Example1\Class1)#19 (1) {
 *     ["class2"]=>
 *     object(Example\Example1\Class2)#14 (0) {
 *     }
 * }
 */
$class = $container->get(Class1::class);

/**
 * Fatal error: Uncaught Temkaa\SimpleContainer\Exception\NonAutowirableClassException: Cannot autowire class
 * "Example\Example1\Class3" as it is in "exclude" config parameter.
 */
$container->get(Class3::class);

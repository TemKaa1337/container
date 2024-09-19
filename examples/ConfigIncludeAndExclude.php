<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\ConfigIncludeAndExclude\Class1;
use Example\ConfigIncludeAndExclude\Class3;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/ConfigIncludeAndExclude/')
    ->exclude(__DIR__.'/ConfigIncludeAndExclude/Class3.php')
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
 * Fatal error: Uncaught Temkaa\Container\Exception\NonAutowirableClassException: Cannot autowire class
 * "Example\Example1\Class3" as it is in "exclude" config parameter.
 */
$container->get(Class3::class);

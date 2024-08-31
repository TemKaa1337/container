<?php

declare(strict_types=1);

namespace Example;

use Example\AttributeFactory\Class1;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

require __DIR__.'/../vendor/autoload.php';

$config = ConfigBuilder::make()->include(__DIR__.'/AttributeFactory/')->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\AttributeFactory\Class1)#27 (4) {
 *     ["class3":"Example\AttributeFactory\Class1":private]=>
 *     object(Example\AttributeFactory\Class3)#31 (0) {
 *     }
 *     ["stringVar":"Example\AttributeFactory\Class1":private]=>
 *     string(10) "string_var"
 *     ["intVar":"Example\AttributeFactory\Class1":private]=>
 *     int(1)
 *     ["tagged":"Example\AttributeFactory\Class1":private]=>
 *     array(1) {
 *     [0]=>
 *         object(Example\AttributeFactory\Class4)#29 (0) {
 *         }
 *     }
 * }
 */
$class = $container->get(Class1::class);

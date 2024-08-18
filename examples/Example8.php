<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\Example8\Collector;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/Example8/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\Example8\Collector)#24 (1) {
 *     ["objects"]=>
 *     array(2) {
 *         [0]=>
 *         object(Example\Example8\Class1)#14 (0) {
 *         }
 *         [1]=>
 *         object(Example\Example8\Class2)#21 (0) {
 *         }
 *     }
 * }
 */
$class = $container->get(Collector::class);

<?php

declare(strict_types=1);

namespace Example;

use Example\AttributeInstance\Collector;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;

require __DIR__.'/../vendor/autoload.php';

$config = ConfigBuilder::make()->include(__DIR__.'/AttributeInstance/')->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\AttributeInstance\Collector)#40 (1) {
 *     ["object"]=>
 *     object(Example\AttributeInstance\Class1)#37 (0) {
 *     }
 * }
 */
$class = $container->get(Collector::class);

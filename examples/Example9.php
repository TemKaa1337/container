<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/Example9/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\Example9\Class1)#17 (0) {
 * }
 */
$class = $container->get('alias');

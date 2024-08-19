<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\ConfigAndAttributeClassAlias\Class2;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'/ConfigAndAttributeClassAlias/')
    ->bindClass(
        ClassBuilder::make(Class2::class)
            ->alias('class_2_alias')
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\Example9\Class1)#19 (0) {
 * }
 */
$class = $container->get('class_1_alias');

/**
 * object(Example\Example9\Class2)#20 (0) {
 * }
 */
$class = $container->get('class_2_alias');


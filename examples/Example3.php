<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\Example3\Class1;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

putenv('ENV_VAR_1=env_var_1_value');
putenv('ENV_VAR_2=2');

$config = ConfigBuilder::make()
    ->include(__DIR__.'/Example3/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\Example2\Class1)#17 (2) {
 *     ["variable1"]=>
 *     string(16) "variable_1_value"
 *     ["variable2"]=>
 *     int(2)
 * }
 */
$class = $container->get(Class1::class);

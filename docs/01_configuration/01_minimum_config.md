# Base config

##### The minimum config you need to use this package is:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;

$config = ConfigBuilder::make()
    // all class absolute paths that must be included
    // in example below, if php script with config builder is located in `/home/user/project/example-project/project`
    // the files will be included from `/home/user/project/example-project/project/../../some/path` path
    ->include(__DIR__.'../../some/path/')
    ->include(__DIR__.'../../some/ClassName.php')
    // all class absolute paths that must be excluded
    ->exclude(__DIR__.'../../some2/')
    ->exclude(__DIR__.'../../some2/ClassName2.php')
    ->build();

// With such config container will load all needed classes and inject all needed dependencies
$container = ContainerBuilder::make()->add($config)->build();
```

##### Also, you have an option to add multiple configs:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;

$config = ConfigBuilder::make()->build();
$container = ContainerBuilder::make()->add($config)->build();

// or if you need multiple config files (for example for vendor package):
$config1 = ConfigBuilder::make()->build();
$config2 = ConfigBuilder::make()->build();
$config3 = ConfigBuilder::make()->build();

$container = ContainerBuilder::make()
  ->add($config1)
  ->add($config2)
  ->add($config3)
  ->build();
```

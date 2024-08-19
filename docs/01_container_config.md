##### Config example:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;

$config = ConfigBuilder::make()
    // all class absolute paths that must be included
    // in example below, if php script with config builder is located in `/home/user/project/example-project/project`
    // the files will be included from `/home/user/project/example-project/project/../../some/path` path
    ->include(__DIR__.'../../some/path/')
    ->include(__DIR__.'../../some/ClassName.php')
    // all class absolute paths that must be excluded
    ->exclude(__DIR__.'../../some2/')
    ->exclude(__DIR__.'../../some2/ClassName2.php')
    // list of global variable bindings which will be bound to variables with same name across all of he autowired classes
    ->bindVariable('variableName1', 'variableValue1')
    ->bindVariable('variableName2', 'env(ENV_VAR_2)')
    // bounded interfaces
    // in this case, if you call `$container->get(SomeInterface::class)` the `SomeInterfaceImplementation` object will be provided
    ->bindInterface(SomeInterface::class, SomeInterfaceImplementation::class)
    ->bindClass(
        // class info binding
        ClassBuilder::make(SomeClass::class)
            // bound variables in this class context only
            ->bindVariable('$variableName1', 'variable_value')
            ->bindVariable('variableName2', 'env(ENV_VARIABLE)')
            ->bindVariable('variableName3', 'env(ENV_VARIABLE_1)_env(ENV_VARIABLE_2)')
            ->bindVariable('$variableName4', '!tagged tag_name')
            // list of class aliases
            ->alias('clasS_alias_1')
            ->alias('clasS_alias_2')
            // decorated class
            // in this case `SomeClass` is decorating `DecoratedClass` and object of `DecoratedClass` is passed 
            // in SomeClass's `$inner` parameter
            ->decorates(id: DecoratedClass::class, signature: '$inner', priority: 2)
            // is class singleton or not (true by default)
            // if `false` is provided, when you call `$container->get(SomeClass::class)` you will receive a new object
            // instance every time, moreover if this class is used in other classes, a new instance will be created
            // on every class usage
            ->singleton(false)
            // class tags
            ->tag('tag1')
            ->tag('tag2')
            ->build()
    )
    ->build();
```

##### Adding multiple configs:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;

$config = ConfigBuilder::make()->build();
$container = (new ContainerBuilder())->add($config)->build();

// or if you need multiple config files (for example for vendor package):
$config1 = ConfigBuilder::make()->build();
$config2 = ConfigBuilder::make()->build();
$config3 = ConfigBuilder::make()->build();

$container = ContainerBuilder::make()
  ->add($config1)
  ->add($config2)
  ->add($config3)
  ->build();

/** @var ClassName $object */
$object = $container->get(ClassName::class);

// or if you have the class which has alias (from Alias attribute) then you can get its instance by alias
$object = $container->get('class_alias');

// or if you have registered interface implementation in config you can get class which implements interface by calling
$object = $container->get(InterfaceName::class);
```

##### Binding env variables:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;

$variables = [
    'ENV_VAR_1' => 'env_var_1_value',
    'ENV_VAR_2' => 'env_var_2_value',
    'ENV_VAR_3' => 'env(ENV_VAR_2)',
];

/**
 * when binding env variables either you can:
 * 1. bind single env variables, e.g. `env(ENV_VAR_1)` = `env_var_1_value`
 * 2. glue multiple env variables, e.g. `env(ENV_VAR_1)_env(ENV_VAR_2)` = `env_var_1_value_env_var_2_value`
 * 3. pass referenced env variables, e.g. `env(ENV_VAR_3)` = `env_var_2_value`
 */

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/')
    ->exclude(__DIR__.'../../some2/ClassName2.php')
    ->bindVariable('variableName2', 'env(ENV_VAR_3)')
    ->build();
```
##### Important notes:
- If you type hinted some class in class arguments of class, which is in `include` section
  and which argument is neither in `include` and `exclude` sections, it will also be autowired.

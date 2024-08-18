### This is a simple DI Container implementation.

##### Installation:
```composer
composer require temkaa/simple-container
```

##### Usage
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Container;
use Temkaa\SimpleContainer\Container\Builder;
use Temkaa\SimpleContainer\Builder\ConfigBuilder;

// you need to provide SplFileInfo of config file to builder
$config = ConfigBuilder::make()->build();
$container = (new Builder())->add($config)->compile();

// or if you need multiple config files (for example for vendor package, why not?):
$config1 = ConfigBuilder::make()->build();
$config2 = ConfigBuilder::make()->build();
$config3 = ConfigBuilder::make()->build();

$container = (new Builder())
  ->add($config1)
  ->add($config2)
  ->add($config3)
  ->compile();

/** @var ClassName $object */
$object = $container->get(ClassName::class);

// or if you have the class which has alias (from Alias attribute) then you can get its instance by alias
$object = $container->get('class_alias');

// or if you have registered interface implementation in config you can get class which implements interface by calling
$object = $container->get(InterfaceName::class);
```

##### Container config example:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;

$config = ConfigBuilder::make()
    # all class absolute paths that must be included
    ->include(__DIR__.'../../some/path/')
    ->include(__DIR__.'../../some/ClassName.php')
    # all class absolute paths that must be excluded
    ->exclude(__DIR__.'../../some2/')
    ->exclude(__DIR__.'../../some2/ClassName2.php')
    # list of global variable bindings which will be bound to variables with same name 
    ->bindVariable('variableName1', 'variableValue1')
    ->bindVariable('variableName2', 'env(ENV_VAR_2)')
    # bounded interfaces
    ->bindInterface(SomeInterface::class, SomeInterfaceImplementation::class)
    ->bindClass(
        # class info binding
        ClassBuilder::make(SomeClass::class)
            # bound variables in class context
            ->bindVariable('$variableName1', 'variable_value')
            ->bindVariable('variableName2', 'env(ENV_VARIABLE)')
            ->bindVariable('variableName3', 'env(ENV_VARIABLE_1)_env(ENV_VARIABLE_2)')
            ->bindVariable('$variableName4', '!tagged tag_name')
            # decorated class
            ->decorates(id: DecoratedClass::class, signature: '$inner', priority: 2)
            # is class singleton or not (true by default)
            ->singleton(false)
            # class tags
            ->tag('tag1')
            ->tag('tag2')
            ->build()
    )
    ->build();
```

##### Container attributes example:
```php
<?php

declare(strict_types=1);

namespace App;

use Temkaa\SimpleContainer\Attribute\Alias;
use Temkaa\SimpleContainer\Attribute\Autowire;
use Temkaa\SimpleContainer\Attribute\Bind\Parameter;
use Temkaa\SimpleContainer\Attribute\Bind\Tagged;
use Temkaa\SimpleContainer\Attribute\Tag

#[Alias(name: 'class_alias')]
#[Autowire(load: true, singleton: false)]
#[Tag(name: 'tag_name')]
class Example
{
    public function __construct(
        #[Tagged(tag: 'any_tag_name')]
        private readonly iterable $tagged,
        #[Parameter(expression: 'env(INT_VARIABLE)')]
        private readonly int $age,
    ) {
    }
}
```

##### Decorators:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/')
    ->bindInterface(SomeInterface::class, ClassImplementing::class)
    ->build();
```
```php
<?php

declare(strict_types=1);

namespace App;

use Temkaa\SimpleContainer\Attribute\Decorates;
use Temkaa\SimpleContainer\Container\Builder;

interface SomeInterface
{
}

final class ClassImplementing implements SomeInterface
{
}

#[Decorates(id: SomeInterface::class, priority: 0, signature: 'decorated')]
final class Decorator1 implements SomeInterface
{
    public function __construct(
        private readonly SomeInterface $decorated, 
    ) {
    }
}

/**
 * the higher priority is, the closer is decorator to decorated service
 * in current example the result of decorators chain is:
 * Decorator1 decorates Decorator2, Decorator2 decorates ClassImplementing class.
 */
#[Decorates(id: SomeInterface::class, priority: 1)]
final class Decorator2 implements SomeInterface
{
    public function __construct(
        private readonly SomeInterface $inner, 
    ) {
    }
}

final class Collector
{
    public function __construct(
        private readonly SomeInterface $decoratedInterface, 
    ) {
    }
}

$container = (new Builder())->add($configFile)->compile();

/* $object1 = new Collector(new Decorator1(new Decorator2(new ClassImplementing()))); */
$object1 = $container->get(Collector::class);

/* $object2 = new Decorator1(new Decorator2(new ClassImplementing())); */
$object2 = $container->get(SomeInterface::class);
```

##### Important notes:
- if you have type hinted some class in class arguments of class, which is in `include` section 
and which argument is neither in `include` and `exclude` sections, it will also be autowired.


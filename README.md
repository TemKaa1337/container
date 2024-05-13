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

// you need to provide SplFileInfo of config file to builder
$configFile = new SplFileInfo('/path/to/config/file.yaml');

$container = (new Builder())->add($configFile)->compile();

// or if you need multiple config files (for example for vendor package, why not?):
$configFile1 = new SplFileInfo('/path/to/config/file_1.yaml');
$configFile2 = new SplFileInfo('/path/to/config/file_2.yaml');
$configFile3 = new SplFileInfo('/path/to/config/file_3.yaml');

$container = (new Builder())
  ->add($configFile1)
  ->add($configFile2)
  ->add($configFile3)
  ->compile();

/** @var ClassName $object */
$object = $container->get(ClassName::class);

// or if you have the class which has alias (from Alias attribute) then you can get its instance by alias
$object = $container->get('class_alias');

// or if you have registered interface implementation in config you can get class which implements interface by calling
$object = $container->get(InterfaceName::class);
```

##### Container config example:
```yaml
services:
  # list of global variable bindings which will be bound to variables with same name 
  bind:
    $variableName: env(GLOBAL_VARIABLE_VALUE)

  include:
    # all class paths must be relative to config file to allow container find them
    - '/../some/path/ClassName.php'
    - '/../some/path/'
  exclude:
    - '/../some/path/ClassName.php'
    - '/../some/path/'

  # interface binding
  App\SomeInterface: App\SomeInterfaceImplementation

  # class info binding
  App\SomeClass:
    bind:
      $variableName: 'variable_value'
      $variableName2: 'env(ENV_VARIABLE)'
      $variableName3: 'env(ENV_VARIABLE_1)_env(ENV_VARIABLE_2)'
      $variableName4: !tagged tag_name
    decorates:
      id: App\SomeInterface
      priority: 1
      signature: decorated
    singleton: false
    tags: [tag1, tag2, tag3]
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
```yaml
services:
  include:
    - '/../some/path/'

  App\SomeInterface: App\ClassImplementing
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

##### Here are some improvements which will be implemented later:
- refactoring
- add decorator option to bind automatically decorated service if no other arguments exist (with different signature)
- add ability to not bing interfaces if only one interface implementation exists
- improve exception names and messages
- allow binding variables with php/const notation (constant from classes or enums)
- add option for binding objects through config and by attribute
- add env variable processors (allow casting env variable to enums, strings, floats etc.)
- add Required attribute (to inject dependencies in methods)
- reflection caching
- container compiling into cache


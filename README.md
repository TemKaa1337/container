### This is a simple DI Container implementation.

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
  include:
    # all class paths must be relative to config file to allow container find them
    - '/../some/path/ClassName.php'
    - '/../some/path/'
  exclude:
    - '/../some/path/ClassName.php'
    - '/../some/path/'

  interface_bindings:
    interface_name: class_name

  class_bindings:
    class_name:
      bind:
        $variableName: 'variable_value'
        $variableName2: 'env(ENV_VARIABLE)'
        $variableName3: 'env(ENV_VARIABLE_1)_env(ENV_VARIABLE_2)'
        $variableName4: !tagged tag_name
      tags:
        - tag1
        - tag2
        - tag3
```

### Container attributes example:
```php
<?php

declare(strict_types=1);

namespace App;

use Temkaa\SimpleContainer\Attribute\Alias;
use Temkaa\SimpleContainer\Attribute\Bind\Parameter;
use Temkaa\SimpleContainer\Attribute\Bind\Tagged;
use Temkaa\SimpleContainer\Attribute\Tag

#[Tag(name: 'tag_name')]
#[Alias(name: 'class_alias')]
class Example
{
    public function __construct(
        #[Tagged(tag: 'any_tag_name')]
        private readonly iterable $tagged,
        #[Parameter(expression: 'env(INT_VARIABLE)')]
        private readonly int $age,
    ) {}
}

```

##### Important notes:
- all classes for now are singletons, option with instantiating classes multiple times will be added later.
- if you have type hinted some class in class arguments, which is neither in `include` and `exclude` sections, it will also be autowired.

##### Here are some TODOs:
- fix psalm errors
- fix psr-2 to psr-12 if statements
- Refactoring src + refactoring tests
- automatic release tag drafter
- automated git actions tests passing
- refactor
- suppress psalm errors in tests
- add file generator builder

##### Here are some improvements which will be implemented later:
- reflection caching
- container compiling into cache (+ clearing that cache)
- add decorator (both from attributes and config)
- add singleton (both from attributes and config)
- add global var bindings in config (variable name and value which will be bound everywhere with same name)
- add env variable processors (allow casting env variable to enums, strings, floats etc.)


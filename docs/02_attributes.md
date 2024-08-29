# This package provides the following attributes:

### \#[Parameter]
Using this parameter you can bind values to your class properties.

### \#[Tagged]
Using this parameter you can bind an array of objects which are tagged with specified tag.

### \#[Alias]
Using this parameter you can add an alias to class and when you retrieve an object from container, you can use the 
specified alias(es) instead of full class name.

### \#[Autowire]
Using this parameter you can:
1. Mark class as not autowirable (instead of `exclude` config section)
2. Mark object as non-singleton so when retrieving this class from container, the new instance of class will be generated
each time you retrieve this class.

### \#[Decorates]
Using this parameter you can specify a class/interface which you want to decorate.

### \#[Tag]
Using this parameter you can add tags to class.

```php

<?php

declare(strict_types=1);

namespace App;

use phpDocumentor\Reflection\DocBlock\Tags\Example;
use Temkaa\SimpleContainer\Attribute\Alias;
use Temkaa\SimpleContainer\Attribute\Autowire;
use Temkaa\SimpleContainer\Attribute\Bind\Parameter;
use Temkaa\SimpleContainer\Attribute\Bind\Tagged;
use Temkaa\SimpleContainer\Attribute\Decorates;
use Temkaa\SimpleContainer\Attribute\Tag

interface ExampleInterface
{
}

#[Alias(name: 'class_alias')]
#[Autowire(load: true, singleton: false)]
#[Decorates(id: ExampleInterface::class)]
#[Tag(name: 'tag_name')]
class Example implements ExampleInterface
{
    public function __construct(
        #[Tagged(tag: 'any_tag_name')]
        private readonly iterable $tagged,
        #[Parameter(expression: 'env(INT_VARIABLE)')]
        private readonly int $age,
        #[Parameter(expression: SomeEnumCase::CaseOne)]
        private readonly int $enumCase,
    ) {
    }
}
```

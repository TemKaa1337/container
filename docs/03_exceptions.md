# This package throws the following exceptions:

### \#[Temkaa\SimpleContainer\Exception\CircularReferenceException]
1. Thrown when you bounded circular bounded env variables into class property, e.g:
`TEST_VAR_2=env(TEST_VAR_3), TEST_VAR_3=env(TEST_VAR_2)`.
2. Thrown when you bounded circular bounded classes into class property, e.g:
```php
<?php

declare(strict_types=1);

class TestClass1
{
    public function __construct(
        private readonly TestClass2 $class,
    ) {
    }
}

class TestClass2
{
    public function __construct(
        private readonly TestClass1 $class,
    ) {
    }
}
```

### \#[Temkaa\SimpleContainer\Exception\ClassNotFoundException]
Thrown when container tries to instantiate class which does not exist.

### \#[Temkaa\SimpleContainer\Exception\DuplicatedEntryAliasException]
Thrown when 2 different classes have the same alias.

### \#[Temkaa\SimpleContainer\Exception\EntryNotFoundException]
Thrown when you are trying to retrieve a class which is not present in container.

### \#[Temkaa\SimpleContainer\Exception\NonAutowirableClassException]
Thrown when container tries to resolve parameter/class which is internal/in excluded config list/marked with 
`Autowire(load: false)` attribute.

### \#[Temkaa\SimpleContainer\Exception\UninstantiableEntryException]
Thrown when container tries to resolve class which is `!$reflection->isInstantiable()`.

### \#[Temkaa\SimpleContainer\Exception\UnresolvableArgumentException]
Thrown when container cannot resolve class constructor argument, e.g.:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Attribute\Bind\Parameter;

class TestClass1
{
}

class TestClass2
{
}

class TestClass3
{
    public function __construct(
        // container cant explicitly understand what parameter should be here
        private readonly TestClass1|TestClass2 $class,
    ) {
    }
}
```

### \#[Temkaa\SimpleContainer\Exception\UnsupportedCastTypeException]
Thrown when, for example, you are trying to bind incompatible types, e.g.:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Attribute\Bind\Parameter;

class TestClass2
{
    public function __construct(
        #[Parameter(expression: 'non_castable_to_int_value')]
        private readonly int $class,
    ) {
    }
}
```

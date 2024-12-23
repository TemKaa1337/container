# Using decorators

This package provides option to use decorator design pattern with ease. You can decorate objects by interfaces.

Example using config:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;

interface SomeInterface
{
}

final readonly class Decorator1 implements SomeInterface
{
    public function __construct(
        /**
         * @var ClassImplementingInterface $class
         */
        private SomeInterface $decorated,
        private string $someValue, 
    ) {
    }
}

final readonly class Decorator2 implements SomeInterface
{
    public function __construct(
        /**
         * @var Decorator1 $class
         */
        private SomeInterface $class,
    ) {
    }
}

final readonly class ClassImplementingInterface implements SomeInterface
{
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->configure(
        ClassBuilder::make(Decorator1::class)
            // here you need to specify what you want to decorate: interface or abstract class
            // the second parameter contains priority of nested decorators, the higher priority - the closer to original 
            // the algorithm of how decorator works is it searches for argument type which is equal to that you provided
            // as `id` argument in `decorates` method, e.g. in this example it will search for `SomeInterface` argument type   
            ->decorates(id: SomeInterface::class, priority: 1)
            ->bindVariable('someValue', 'someValue')
            ->build()
    )
    ->configure(
        ClassBuilder::make(Decorator2::class)
            ->decorates(id: SomeInterface::class)
            ->build()
    )
    ->build();

// Note that container can auto discover interface implementations, in above example there is only one `true`
// interface implementation: `ClassImplementingInterface`, and this class is the root interface implementor.
$container = ContainerBuilder::make()->add($config)->build();

/**
 * @var Decorator2 $class
 */
$class = $container->get(SomeInterface::class);
assert($class instanceof Decorator2);
assert($class->decorated instanceof Decorator1);
assert($class->decorated->class instanceof ClassImplementingInterface);
```

Example using attributes:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Attribute\Decorates;
use Temkaa\Container\Attribute\Bind\Parameter;

interface SomeInterface
{
}

// the same logic applies to attributes, container will search for `SomeInterface` type in constructor argument list
#[Decorates(id: SomeInterface::class, priority: 1)]
final readonly class Decorator1 implements SomeInterface
{
    public function __construct(
        /**
         * @var ClassImplementingInterface $class
         */
        private SomeInterface $decorated,
        #[Parameter('someValue')]
        private string $someValue, 
    ) {
    }
}

#[Decorates(id: SomeInterface::class)]
final readonly class Decorator2 implements SomeInterface
{
    public function __construct(
        /**
         * @var Decorator1 $class
         */
        private SomeInterface $class,
    ) {
    }
}

final readonly class ClassImplementingInterface implements SomeInterface
{
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * @var Decorator2 $class
 */
$class = $container->get(SomeInterface::class);
assert($class instanceof Decorator2);
assert($class->decorated instanceof Decorator1);
assert($class->decorated->class instanceof ClassImplementingInterface);
```

Example with required method calls:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Attribute\Decorates;
use Temkaa\Container\Attribute\Bind\Parameter;
use Temkaa\Container\Attribute\Bind\Required;

interface SomeInterface
{
}

#[Decorates(id: SomeInterface::class, priority: 1)]
final readonly class Decorator1 implements SomeInterface
{
    private SomeInterface $decorated;

    private string $someValue;

    #[Required]
    public function setDecorator(SomeInterface $decorated, #[Parameter('someValue')] string $someValue): void
    {
        $this->decorated = $decorated;
        $this->someValue = $someValue; 
    }
}

#[Decorates(id: SomeInterface::class)]
final readonly class Decorator2 implements SomeInterface
{
    private SomeInterface $class;

    #[Required]
    public function setDecorator(SomeInterface $class): void
    {
        $this->class = $class;
    }
}

final readonly class ClassImplementingInterface implements SomeInterface
{
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

// the result is the same as in `Config example` or in `Attribute example`
// the principle here is the same, you required method has only 1 parameter - it will be automatically bounded, but if
// 2 or more - you need to specify correct argument name
$class = $container->get(SomeInterface::class);
assert($class instanceof Decorator2);
assert($class->decorated instanceof Decorator1);
assert($class->decorated->class instanceof ClassImplementingInterface);
```

Example with factories:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Attribute\Decorates;
use Temkaa\Container\Attribute\Bind\Parameter;
use Temkaa\Container\Attribute\Bind\Required;
use Temkaa\Container\Attribute\Factory;

interface SomeInterface
{
}

#[Decorates(id: SomeInterface::class, priority: 1)]
#[Factory(id: Decorator1Factory::class, method: 'create')]
final readonly class Decorator1 implements SomeInterface
{
    public function __construct(
        private SomeInterface $decorated,
        private string $someValue,
    ) {
    }
}

final readonly class Decorator1Factory
{
    // container knows that this class is decorator factory, and decorators hierarchy is: 
    // Decorator2 -> Decorator1 -> ClassImplementingInterface, so here will be passed ClassImplementingInterface instance
    public static function create(SomeInterface $decorator2, #[Parameter('string')] string $value): Decorator1
    {
        return new Decorator1($decorator2, $value);
    }
}

#[Decorates(id: SomeInterface::class)]
final readonly class Decorator2 implements SomeInterface
{
    private SomeInterface $class;

    #[Required]
    public function setDecorator(SomeInterface $class): void
    {
        $this->class = $class;
    }
}

final readonly class ClassImplementingInterface implements SomeInterface
{
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

// the result is the same as in `Config example` or in `Attribute example`
// the principle here is the same, if factory method has only 1 parameter - it will be automatically bounded, but if
// 2 or more - you need to specify correct argument name
$class = $container->get(SomeInterface::class);
assert($class instanceof Decorator2);
assert($class->decorated instanceof Decorator1);
assert($class->decorated->class instanceof ClassImplementingInterface);
```

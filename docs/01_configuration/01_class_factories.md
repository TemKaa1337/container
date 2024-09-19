# Using class factories

If you have some difficult or encapsulated logic of class instantiation, you can use class factories. Using this feature
you declare a class which is responsible for object instantiation.

Example using config:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Builder\Config\Class\FactoryBuilder;

final readonly class Class1
{
    public function __construct(
        private string $variable,
        private Class2 $class2,
    ) {
    }
}

final readonly class Class2
{
    public function __construct(
        private Class3 $class3,
    ) {
    }
}

final readonly class Class3
{
    // class can also have self factory which must be static
    public static function createSelf(): self
    {
        return new self();
    }
}

final readonly Factory
{
    public function __construct(
        private Class3 $class3,
    ) {   
    }

    // if factory method is static, object is not instantiated and all needed variables for object creation are passed
    // to factory method, you can bind env variables/enums/other classes/tagged iterators
    public static function createClass1(string $envVar, Class2 $class2): Class1
    {
        return new Class1($envVar, $class2);
    }

    // if factory method is dynamic, factory object is instantiated, all constructor arguments are resolved and after
    // that factory method is called, which also can have variable bindings and so on
    public function createClass2(): Class2
    {
        return new Class2($this->class3);
    }
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->bindClass(
        ClassBuilder::make(Class1::class)
            ->factory(
                FactoryBuilder::make(id: Factory::class, method: 'createClass1')
                    // note that factories have their own bounded variable scope
                    ->bindVariable('envVar', 'some_env_var')
                    ->build()
            )
    )
    ->bindClass(
        ClassBuilder::make(Class2::class)
            ->factory(
                FactoryBuilder::make(id: Factory::class, method: 'createClass2')->build()
            )
    )
    ->bindClass(
        ClassBuilder::make(Class3::class)
            ->factory(
                FactoryBuilder::make(id: Class3::class, method: 'createSelf')->build()
            )
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$class1 = $container->get(Class1::class);
$class2 = $container->get(Class2::class);
$class3 = $container->get(Class3::class);
```

Example using attributes:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Bind\Parameter;
use Temkaa\Container\Builder\Config\Class\FactoryBuilder;
use Temkaa\Container\Attribute\Factory;

#[Factory(id: Factory::class, method: 'createClass1')]
final readonly class Class1
{
    public function __construct(
        private string $variable,
        private Class2 $class2,
    ) {
    }
}

#[Factory(id: Factory::class, method: 'createClass2')]
final readonly class Class2
{
    public function __construct(
        private Class3 $class3,
    ) {
    }
}

#[Factory(id: self::class, method: 'createSelf')]
final readonly class Class3
{
    // class can also have self factory which must be static
    public static function createSelf(): self
    {
        return new self();
    }
}

final readonly Factory
{
    public function __construct(
        private Class3 $class3,
    ) {   
    }

    public static function createClass1(#[Parameter('some_env_var')] string $envVar, Class2 $class2): Class1
    {
        return new Class1($envVar, $class2);
    }

    public function createClass2(): Class2
    {
        return new Class2($this->class3);
    }
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$class1 = $container->get(Class1::class);
$class2 = $container->get(Class2::class);
$class3 = $container->get(Class3::class);
```

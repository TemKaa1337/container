# Using required method calls

Using this feature you can ask container to call some methods on object after it is created. This can be done for 
injecting some common dependencies, e.g. when you have trait with injected logger/validator or for settings some
configuration after object is created.

Example using config:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;

final class Class1
{
    private Class2 $class2;
    private string $param;

    public function __construct(
        private readonly Class3 $class3,
    ) {
    }

    // this method will be called after `Class1` is instantiated
    public function setClass(Class2 $class2, string $param): self
    {
        $this->class2 = $class2;
        $this->param = $param;
    }
}

final readonly class Class2
{
}

final readonly class Class3
{
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->configure(
        ClassBuilder::make(Class1::class)
            // you can bind variables for required method calls with class bound variables
            ->bindVariable('param', 'some_param')
            ->call('setClass')
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$class1 = $container->get(Class1::class);
```

Example using attributes:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Bind\Parameter;
use Temkaa\Container\Attribute\Bind\Required;

final class Class1
{
    private Class2 $class2;
    private string $param;

    public function __construct(
        private readonly Class3 $class3,
    ) {
    }

    #[Required]
    public function setClass(Class2 $class2, #[Parameter('param')] string $param): self
    {
        $this->class2 = $class2;
        $this->param = $param;
    }
}

final readonly class Class2
{
}

final readonly class Class3
{
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$class1 = $container->get(Class1::class);
```
Please note here that required static method calls are not  supported, you can call only dynamic methods. 

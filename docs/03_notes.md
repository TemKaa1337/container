# Important notes:
- If you type hinted some class in class arguments of class, which is in `include` section
  and which argument is neither in `include` and `exclude` sections, it will also be autowired;
- When you decorate any class (doesn't matter with config or with attribute) and in decorator class you have only one
  constructor argument, then you can omit `signature` parameter and name this argument as you want, in this case container
  will understand that your decorator have only one constructor argument which is decorated class;
- If you want to create a tagged iterator of all classes which implement specific interface, you can tag an interface
  with specific `#[Tag]` attribute as all classes which implement any tagged interface, also inherit tags from this interface.
  Then implement this interface with classes you want to create iterator with and simply bind
  tagged iterator from config or from `#[TaggedIterator]` attribute, OR the second way (and more preferable) you can use
  `InstanceOfIterator` attribute which will automatically find all classes which implement interface/extend provided class/interface;
- This package supports default class/factory/required arguments. It means that if your class has a dependency with default
  parameter, first of all container will try to instantiate type-hinted argument (if argument type is not built-in),
  and if container cant instantiate it, container will use the default value you provided;
- This package provides auto discover of bounded interfaces and interface decorators.
  For example, let's say you have one interface and one class, which implements this interface:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder

interface TestInterface
{
}

class TestClass impelments TestInterface
{

}

$config = ConfigBuilder::make()->include(__DIR__.'some_path_with_above_classes')->build();

$container = ContainerBuilder::make()->add($config)->build();

// in this case `TestInterface` has only one implementation which is 'accessible' (is in include config section)
// so when you retrieve `TestInterface` from container you will receive `TestClass` object
/**
 * object(TestClass)#18 (0) {
 * }
 */
$class = $container->get(TestInterface::class);
```
But if you have more than 1 implementation and do not bound specific class to this interface, you will receive
`Temkaa\SimpleContainer\Exception\Config\EntryNotFoundException`, example:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder

interface TestInterface
{
}

class TestClass1 impelments TestInterface
{

}
class TestClass2 impelments TestInterface
{

}

$config = ConfigBuilder::make()->include(__DIR__.'some_path_with_above_classes')->build();
$container = ContainerBuilder::make()->add($config)->build();

/**
 * Fatal error: Uncaught Temkaa\SimpleContainer\Exception\EntryNotFoundException: Class "TestInterface" is not found.
 */
$class = $container->get(TestInterface::class);
```
The same story about decorators, lets say you have the following code:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder
use Temkaa\SimpleContainer\Attribute\Decorates;

interface TestInterface
{
}

class TestClass1 impelments TestInterface
{
}

#[Decorates(id: TestInterface::class, priority: 1)]
class TestClass2 impelments TestInterface
{
    public function __construct(
        private readonly TestInterface $decorated,
    ) {
    }
}

#[Decorates(id: TestInterface::class, priority: 2)]
class TestClass3 impelments TestInterface
{
    public function __construct(
        private readonly TestInterface $decorated,
    ) {
    }
}

$config = ConfigBuilder::make()->include(__DIR__.'some_path_with_above_classes')->build();
$container = ContainerBuilder::make()->add($config)->build();

// in this case `TestInterface` has only one implementation which is 'accessible' (is in include config section)
// and all other interface implementations decorate the one and only 'true' implementation, so when you retrieve
// `TestInterface` from container you will receive `TestClass2` object
/**
 * object(Example\Example5\Class2)#31 (1) {
 *     ["decorated"]=>
 *     object(Example\Example5\Class3)#28 (1) {
 *         ["decorated"]=>
 *         object(Example\Example5\Class1)#29 (0) {
 *         }
 *     }
 * }
 */
$class = $container->get(TestInterface::class);
```
But if you have more than 1 interface implementation which does not decorate the implemented interface, you will receive
`Temkaa\SimpleContainer\Exception\Config\EntryNotFoundException`, example:
```php

<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder
use Temkaa\SimpleContainer\Attribute\Decorates;

interface TestInterface
{
}

class TestClass1 impelments TestInterface
{
}
class TestClass2 impelments TestInterface
{
}

#[Decorates(id: TestInterface::class, priority: 1)]
class TestClass3 impelments TestInterface
{
    public function __construct(
        private readonly TestInterface $decorated,
    ) {
    }
}

#[Decorates(id: TestInterface::class, priority: 2)]
class TestClass4 impelments TestInterface
{
    public function __construct(
        private readonly TestInterface $decorated,
    ) {
    }
}

$config = ConfigBuilder::make()->include(__DIR__.'some_path_with_above_classes')->build();
$container = ContainerBuilder::make()->add($config)->build();

// in this case `TestInterface` has 2 implementations: `TestClass1` and `TestClass2` which implement interface and do
// not decorate it
/**
 * Fatal error: Uncaught Temkaa\SimpleContainer\Exception\Config\EntryNotFoundException: Could not find interface 
 * implementation for "TestInterface".
 */
$class = $container->get(TestInterface::class);
```

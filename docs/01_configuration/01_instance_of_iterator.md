# Using instance of iterators

This works pretty much the same as `TaggedIterator` but with all classes which are instances of specific class/interface.
You can pass abstract classes there, interfaces and regular classes.

Example binding instance of classes using config:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use \Temkaa\SimpleContainer\Model\Bind\Instance;
use Generator;
use LogicException;

interface DataProcessor
{
    public function process(iterable $data): void;
    
    public function supports(iterable $data): bool;
}

final readonly class ArrayProcessor implements DataProcessor
{
    public function process(iterable $data): void
    {
        // some data processing here
    }
    
    public function supports(iterable $data): bool
    {
        return is_array($data);
    }
}

final readonly class GeneratorProcessor implements DataProcessor
{
    public function process(iterable $data): void
    {
        // some data processing here
    }
    
    public function supports(iterable $data): bool
    {
        return $data instanceof Generator;
    }
}

final readonly class Processor
{
    public function __construct(
       /**
        * @var ProcessorInterface[] $processors
        */
        public array $processors,
    ) {
    }
    
    public function process(iterable $data): void
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($data)) {
                $processor->process($data);
                
                return;
            }
        }
        
        throw new LogicException('Cannot find suitable processor.');
    }
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->bindClass(
        ClassBuilder::make(Processor::class)
            // here you say that this argument is array of classes which implement `DataProcessor` interface
            ->bindVariable('$processors', new Instance(DataProcessor::class))
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$data = [/* some data here */];

$processor = $container->get(Processor::class);
$processor->process($data);
```

Example tagging interfaces using attributes:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Attribute\Tag;
use Temkaa\SimpleContainer\Attribute\Bind\InstanceOfIterator;
use Generator;
use LogicException;

interface DataProcessor
{
    public function process(iterable $data): void;
    
    public function supports(iterable $data): bool;
}

final readonly class ArrayProcessor implements DataProcessor
{
    public function process(iterable $data): void
    {
        // some data processing here
    }
    
    public function supports(iterable $data): bool
    {
        return is_array($data);
    }
}

final readonly class GeneratorProcessor implements DataProcessor
{
    public function process(iterable $data): void
    {
        // some data processing here
    }
    
    public function supports(iterable $data): bool
    {
        return $data instanceof Generator;
    }
}

final readonly class Processor
{
    public function __construct(
       /**
        * @var ProcessorInterface[] $processors
        */
        #[InstanceOfIterator(DataProcessor::class)]
        public array $processors,
    ) {
    }
    
    public function process(iterable $data): void
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($data)) {
                $processor->process($data);
                
                return;
            }
        }
        
        throw new LogicException('Cannot find suitable processor.');
    }
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$data = [/* some data here */];

$processor = $container->get(Processor::class);
$processor->process($data);
```

Example with abstract classes:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Attribute\Bind\InstanceOfIterator;
use Generator;
use LogicException;

abstract class AbstractDataProcessor
{
    abstract public function process(iterable $data): void;
    
    abstract public function supports(iterable $data): bool;
}

final readonly class ArrayProcessor extends AbstractDataProcessor
{
    public function process(iterable $data): void
    {
        // some data processing here
    }
    
    public function supports(iterable $data): bool
    {
        return is_array($data);
    }
}

final readonly class GeneratorProcessor extends AbstractDataProcessor
{
    public function process(iterable $data): void
    {
        // some data processing here
    }
    
    public function supports(iterable $data): bool
    {
        return $data instanceof Generator;
    }
}

final readonly class Processor
{
    public function __construct(
       /**
        * @var AbstractDataProcessor[] $processors
        */
        #[InstanceOfIterator(AbstractDataProcessor::class)]
        public array $processors,
    ) {
    }
    
    public function process(iterable $data): void
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($data)) {
                $processor->process($data);
                
                return;
            }
        }
        
        throw new LogicException('Cannot find suitable processor.');
    }
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$data = [/* some data here */];

$processor = $container->get(Processor::class);
$processor->process($data);
```

Example with regular classes:
```php
<?php

declare(strict_types=1);

use Temkaa\SimpleContainer\Builder\ConfigBuilder;
use Temkaa\SimpleContainer\Builder\Config\ClassBuilder;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Attribute\Bind\InstanceOfIterator;
use Generator;
use LogicException;

class DataProcessor
{
    public function process(iterable $data): void
    {
    
    }
    
    public function supports(iterable $data): bool
    {
    
    }
}

final readonly class ArrayProcessor extends DataProcessor
{
}

final readonly class GeneratorProcessor extends DataProcessor
{
}

final readonly class Processor
{
    public function __construct(
       /**
        * @var DataProcessor[] $processors
        */
        #[InstanceOfIterator(DataProcessor::class)]
        public array $processors,
    ) {
    }
    
    public function process(iterable $data): void
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($data)) {
                $processor->process($data);
                
                return;
            }
        }
        
        throw new LogicException('Cannot find suitable processor.');
    }
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$data = [/* some data here */];

$processor = $container->get(Processor::class);
$processor->process($data);
```

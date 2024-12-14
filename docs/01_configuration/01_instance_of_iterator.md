# Using instance of iterators

This works pretty much the same as `TaggedIterator` but with all classes which are instances of specific class/interface.
You can pass abstract classes there, interfaces and regular classes.

Example binding instance of classes using config:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
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
    /**
     * @param list<ProcessorInterface> $processors
     */
    public function __construct(
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
    ->configure(
        ClassBuilder::make(Processor::class)
            // here you say that this argument is array of classes which implement `DataProcessor` interface
            ->bindVariable('$processors', new InstanceOfIterator(DataProcessor::class))
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

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Tag;
use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
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
    /**
     * @param list<ProcessorInterface> $processors
     */
    public function __construct(
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

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
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
    /**
     * @param list<AbstractDataProcessor> $processors
     */
    public function __construct(
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

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
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
    /**
     * @param list<DataProcessor> $processors
     */
    public function __construct(
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
This package also allows to create some kind of `composite` classes with `InstanceOfIterator`:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Tag;
use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
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

final readonly class Processor implements DataProcessor
{
    /**
     * @param list<ProcessorInterface> $processors
     */
    public function __construct(
        // here we say we want to receive all classes which implement `DataProcessor` interface except this one
        #[InstanceOfIterator(DataProcessor::class, exclude: [self::class])]
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

final readonly class Entrypoint
{
    public function __construct(
       private DataProcessor $processor,
    ) {
    }
    
    public function run(iterable $data): void
    {
        $this->processor->process($data);
    }
}


$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->bindInterface(DataProcessor::class, Processor::class)
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$data = [/* some data here */];

$processor = $container->get(Processor::class);
$processor->process($data);
```
You can also choose what format you want this array of objects to be:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;
use Generator;
use LogicException;

interface EventProcessorInterface
{
    public function process(array $event): void;
}

final readonly class UserCreatedEventProcessor implements EventProcessorInterface
{
    public function process(array $event): void
    {
        // some processing here
    }
}

final readonly class UserDeletedEventProcessor implements EventProcessorInterface
{
    public function process(array $event): void
    {
        // some processing here
    }
}

final readonly class Processor
{
    /**
     * @param array<string, ProcessorInterface> $processors
     */
    public function __construct(
        private array $processors,
    ) {
    }
    
    /**
     * @param array{name: 'user.deleted'|'user.created', data: array} $event
     * @return void
     */
    public function process(array $event): void
    {
        if (!$processor = $this->processors[$event['name']] ?? null) {
            throw new LogicException('Cannot find suitable processor.');
        }

        $processor->process($event);
    }
}

// the same functionality is available with InstanceOfIterator attribute
$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->configure(
        ClassBuilder::make(Processor::class)
            // in example above this version of configuration is used
            // in this case the result of this configuration is:
            // ['user.created' => object(UserCreatedEventProcessor), 'user.deleted' => object(UserDeletedEventProcessor)]
            ->bindVariable(
                '$processors',
                new InstanceOfIterator(
                    EventProcessorInterface::class,
                    format: IteratorFormat::ArrayWithCustomKey,
                    exclude: [Processor::class]
                    customFormatMapping: [
                        UserCreatedEventProcessor::class => 'user.created',
                        UserDeletedEventProcessor::class => 'user.deleted',
                    ]
                )
            )
            // or you can use this configuration
            // in this case the result of this configuration is:
            // [UserCreatedEventProcessor::class => object(UserCreatedEventProcessor), UserDeletedEventProcessor::class => object(UserDeletedEventProcessor)]
            ->bindVariable(
                '$processors',
                new InstanceOfIterator(
                    EventProcessorInterface::class,
                    format: IteratorFormat::ArrayWithClassNamespaceKey,
                    exclude: [Processor::class]
                )
            )
            // or you can use this configuration
            // in this case the result of this configuration is:
            // ['UserCreatedEventProcessor' => object(UserCreatedEventProcessor), 'UserDeletedEventProcessor' => object(UserDeletedEventProcessor)]
            ->bindVariable(
                '$processors',
                new InstanceOfIterator(
                    EventProcessorInterface::class,
                    format: IteratorFormat::ArrayWithClassNameKey,
                    exclude: [Processor::class]
                )
            )
            // or you can use this configuration (this is the default configuration)
            // in this case the result of this configuration is:
            // [object(UserCreatedEventProcessor), object(UserDeletedEventProcessor)]
            ->bindVariable(
                '$processors',
                new InstanceOfIterator(
                    EventProcessorInterface::class,
                    format: IteratorFormat::List,
                )
            )
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$data = [/* some data here */];

$processor = $container->get(Processor::class);
$processor->process($data);
```

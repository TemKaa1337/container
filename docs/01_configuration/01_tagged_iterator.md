# Using tagged iterators

Sometimes you need to inject a collection of objects which are somehow related with each other or have something similar.
For example, you have a set of operations on data and each operation can support given data set or not. In this case you
want to have a general entrypoint for any data processing.

Example tagging classes using config:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Bind\TaggedIterator;
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
        ClassBuilder::make(ArrayProcessor::class)
            ->tag('data.processor')
            ->build(),
    )
    ->bindClass(
        ClassBuilder::make(GeneratorProcessor::class)
            ->tag('data.processor')
            ->build(),
    )
    ->bindClass(
        ClassBuilder::make(Processor::class)
            // here you say that this argument is tagged iterator of classes with `data.processor` tag
            ->bindVariable('$processors', new TaggedIterator('data.processor'))
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
use Temkaa\Container\Attribute\Bind\TaggedIterator;
use Generator;
use LogicException;

// here you specify the tag name for `DataProcessor` interface
// all implementing classes will inherit this tag
#[Tag('data.processor')]
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
        #[TaggedIterator('data.processor')]
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
Example tagging classes using attributes:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Bind\Parameter;
use Temkaa\Container\Attribute\Tag;
use Temkaa\Container\Attribute\Bind\TaggedIterator;
use Generator;
use LogicException;

interface DataProcessor
{
    public function process(iterable $data): void;

    public function supports(iterable $data): bool;
}

#[Tag('data.processor')]
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

#[Tag('data.processor')]
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
        #[TaggedIterator('data.processor')]
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

Example tagging interface using attributes:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Bind\Parameter;
use Temkaa\Container\Attribute\Tag;
use Temkaa\Container\Attribute\Bind\TaggedIterator;
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
        #[TaggedIterator('data.processor')]
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
        ClassBuilder::make(DataProcessor::class)
            ->tag('data.processor')
            ->build()
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

$data = [/* some data here */];

$processor = $container->get(Processor::class);
$processor->process($data);
```
This package also allows to create some kind of `composite` classes with `TaggedIterator`:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Tag;
use Temkaa\Container\Attribute\Bind\TaggedIterator;
use Generator;
use LogicException;

// here you specify the tag name for `DataProcessor` interface
// all implementing classes will inherit this tag
#[Tag('data.processor')]
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

final readonly class Processor implements ProcessorInterface
{
    public function __construct(
       /**
        * @var ProcessorInterface[] $processors
        */
        // here we say we want to receive all classes with `data.processor` tag except this one
        #[TaggedIterator('data.processor', exclude: [self::class])]
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

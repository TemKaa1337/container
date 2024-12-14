<?php

declare(strict_types=1);

namespace Tests\Benchmark;

use PhpBench\Attributes\Assert;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Model\Config;

/**
 * @psalm-suppress InaccessibleProperty
 * @psalm-suppress MissingConstructor
 */
final readonly class CompileTimeExistingClassesBenchmark
{
    private Config $config;

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     */
    #[Assert(expression: 'mode(variant.time.avg) < 70 milliseconds')] // latest best local run - 52ms (67ms in GHA)
    #[BeforeMethods('setUp')]
    #[Iterations(20)]
    #[Revs(20)]
    public function benchCompilesInSuitableTime(): void
    {
        (new ContainerBuilder())->add($this->config)->build();
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     */
    #[Assert(expression: 'mode(variant.time.avg) < 101 milliseconds')] // latest best local run - 91ms (100 in GHA)
    #[BeforeMethods('setUp')]
    public function benchFirstCompilationConsumesSuitableTime(): void
    {
        (new ContainerBuilder())->add($this->config)->build();
    }

    public function setUp(): void
    {
        $this->config = ConfigBuilder::make()
            ->include(__DIR__.'/../Fixture/Benchmark/')
            ->exclude(__DIR__.'/../Fixture/Benchmark/Model/')
            ->build();
    }
}

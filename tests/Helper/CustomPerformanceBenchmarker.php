<?php

declare(strict_types=1);

namespace Tests\Helper;

use Composer\Autoload\ClassLoader;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use function array_keys;
use function microtime;
use function var_dump;

final readonly class CustomPerformanceBenchmarker
{
    public function test(): void
    {
        $start = microtime(true);
        $config = ConfigBuilder::make()
            ->include(__DIR__.'/../Fixture/Benchmark/')
            ->exclude(__DIR__.'/../Fixture/Benchmark/Model/')
            ->build();

        (new ContainerBuilder())->add($config)->build();
        $end = microtime(true);

        var_dump('time elapsed: ' . $end - $start . ' seconds');
    }
}

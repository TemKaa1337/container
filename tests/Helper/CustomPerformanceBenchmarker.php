<?php

declare(strict_types=1);

namespace Tests\Helper;

use Composer\Autoload\ClassLoader;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Util\Extractor\UniqueDirectoryExtractor;
use function array_keys;
use function dirname;
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

    public function testDirs(): void
    {
        $extractor = new UniqueDirectoryExtractor();

        $res = $extractor->extract(
            [
                __DIR__.'/../Fixture/Benchmark/',
                __DIR__.'/../Fixture/Benchmark/',
                __DIR__.'/../Fixture/Benchmark/Model',
                __DIR__.'/../Fixture/Benchmark/Model',
                __DIR__.'/../Fixture/Benchmark/Model/Request',
                __DIR__.'/../',
            ]
        );

        var_dump('dunction result', $res);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Util\Extractor;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Temkaa\Container\Util\Extractor\UniqueDirectoryExtractor;
use function realpath;

final class UniqueDirectoryExtractorTest extends TestCase
{
    public static function getDataForExtractTest(): iterable
    {
        yield [
            [
                __DIR__.'/../Fixture/Benchmark/',
                __DIR__.'/../Fixture/Benchmark/',
                __DIR__.'/../Fixture/Benchmark/Model',
                __DIR__.'/../Fixture/Benchmark/Model',
                __DIR__.'/../Fixture/Benchmark/Model/Request',
                __DIR__.'/../',
            ],
            [
                __DIR__.'/../',
            ],
        ];
        yield [
            [
                __DIR__.'/../Fixture/Benchmark/SomeFileName.php',
                __DIR__.'/../Fixture/Benchmark/',
                __DIR__.'/../Fixture/Benchmark/Model',
                __DIR__.'/../Fixture/Benchmark/Model',
                __DIR__.'/../Fixture/Benchmark/Model/Request',
                __DIR__.'/../',
            ],
            [
                __DIR__.'/../',
            ],
        ];
        yield [
            [
                __DIR__.'/../Fixture/Benchmark/Folder1/SomeFileName.php',
                __DIR__.'/../Fixture/Benchmark/Folder2',
                __DIR__.'/../Fixture/OtherDir/',
                __DIR__.'/../Fixture/Benchmark/Model',
                __DIR__.'/../Fixture/Benchmark/Model',
                __DIR__.'/../Fixture/Benchmark/Model/Request',
            ],
            [
                __DIR__.'/../Fixture/Benchmark/Folder1/SomeFileName.php',
                __DIR__.'/../Fixture/Benchmark/Folder2/',
                __DIR__.'/../Fixture/OtherDir/',
                __DIR__.'/../Fixture/Benchmark/Model/',
            ],
        ];
        yield [
            [
                __DIR__.'/../Fixture/Benchmark/Folder1/SomeFileName.php',
                __DIR__.'/../Fixture/Benchmark/Folder1',
                __DIR__.'/../Fixture/OtherDir/',
                __DIR__.'/../Fixture/Benchmark/Model/Request',
            ],
            [
                __DIR__.'/../Fixture/Benchmark/Folder1/',
                __DIR__.'/../Fixture/OtherDir/',
                __DIR__.'/../Fixture/Benchmark/Model/Request/',
            ],
        ];
        yield [
            [
                __DIR__.'/../Fixture/Benchmark/Folder1/Folder2',
                __DIR__.'/../Fixture/Benchmark/Folder2',
                __DIR__.'/../Fixture/Benchmark/Folder3/Folder1',
                __DIR__.'/../Fixture/Benchmark/Folder4/',
                __DIR__.'/../Fixture/Benchmark/Folder5/Folder1/Folder2',
                __DIR__.'/../Fixture/Benchmark/',
            ],
            [
                __DIR__.'/../Fixture/Benchmark/',
            ],
        ];
        yield [
            [
                (new ReflectionClass(Finder::class))->getFileName(),
            ],
            [
                (new ReflectionClass(Finder::class))->getFileName(),
            ],
        ];
        yield [
            [
                (new ReflectionClass(Finder::class))->getFileName(),
                realpath(__DIR__.'/../../../../vendor/')
            ],
            [
                realpath(__DIR__.'/../../../../vendor/').'/'
            ],
        ];
    }

    #[DataProvider('getDataForExtractTest')]
    public function testExtract(array $directories, array $expectedResultDirectories): void
    {
        self::assertEqualsCanonicalizing(
            $expectedResultDirectories,
            (new UniqueDirectoryExtractor())->extract($directories)
        );
    }
}

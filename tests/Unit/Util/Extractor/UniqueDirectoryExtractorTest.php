<?php

declare(strict_types=1);

namespace Tests\Unit\Util\Extractor;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Temkaa\Container\Util\Extractor\UniqueDirectoryExtractor;
use function realpath;
use function str_replace;
use const DIRECTORY_SEPARATOR;

final class UniqueDirectoryExtractorTest extends TestCase
{
    public static function getDataForExtractTest(): iterable
    {
        yield [
            [
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model/Request'),
                self::normalizePath(__DIR__.'/../'),
            ],
            [
                self::normalizePath(__DIR__.'/..').DIRECTORY_SEPARATOR,
            ],
        ];
        yield [
            [
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/SomeFileName.php'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model/Request'),
                self::normalizePath(__DIR__.'/../'),
            ],
            [
                self::normalizePath(__DIR__.'/..').DIRECTORY_SEPARATOR,
            ],
        ];
        yield [
            [
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Folder1/SomeFileName.php'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Folder2'),
                self::normalizePath(__DIR__.'/../Fixture/OtherDir/'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model/Request'),
            ],
            [
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Folder1/SomeFileName.php'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Folder2').DIRECTORY_SEPARATOR,
                self::normalizePath(__DIR__.'/../Fixture/OtherDir').DIRECTORY_SEPARATOR,
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model').DIRECTORY_SEPARATOR,
            ],
        ];
        yield [
            [
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Folder1/SomeFileName.php'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Folder1'),
                self::normalizePath(__DIR__.'/../Fixture/OtherDir/'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model/Request'),
            ],
            [
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Folder1').DIRECTORY_SEPARATOR,
                self::normalizePath(__DIR__.'/../Fixture/OtherDir').DIRECTORY_SEPARATOR,
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model/Request').DIRECTORY_SEPARATOR,
            ],
        ];
        yield [
            [
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Folder1/Folder2'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Folder2'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Folder3/Folder1'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Folder4/'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Folder5/Folder1/Folder2'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/'),
            ],
            [
                self::normalizePath(__DIR__.'/../Fixture/Benchmark').DIRECTORY_SEPARATOR,
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
                realpath(__DIR__.'/../../../../vendor/'),
            ],
            [
                realpath(__DIR__.'/../../../../vendor/').DIRECTORY_SEPARATOR,
            ],
        ];
        yield [
            [
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Enum/'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Factory'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model/Response/Nested'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model/Response/'),
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model'),
            ],
            [
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Enum').DIRECTORY_SEPARATOR,
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Factory').DIRECTORY_SEPARATOR,
                self::normalizePath(__DIR__.'/../Fixture/Benchmark/Model').DIRECTORY_SEPARATOR,
            ],
        ];
    }

    private static function normalizePath(string $path): string
    {
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    }

    #[DataProvider('getDataForExtractTest')]
    public function testExtract(array $directories, array $expectedResultDirectories): void
    {
        self::assertEqualsCanonicalizing(
            $expectedResultDirectories,
            (new UniqueDirectoryExtractor())->extract($directories),
        );
    }
}

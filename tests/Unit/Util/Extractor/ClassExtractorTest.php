<?php

declare(strict_types=1);

namespace Tests\Unit\Util\Extractor;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Temkaa\Container\Service\Compiler;
use Temkaa\Container\Service\Definition\ArgumentConfigurator;
use Temkaa\Container\Service\Definition\Configurator;
use Temkaa\Container\Service\Definition\Configurator\Argument\BoundVariableConfigurator;
use Temkaa\Container\Service\Definition\Configurator\Argument\InstanceConfigurator;
use Temkaa\Container\Service\Definition\Configurator\Argument\InstanceOfIteratorConfigurator;
use Temkaa\Container\Service\Definition\Configurator\Argument\InterfaceConfigurator as ArgumentInterfaceConfigurator;
use Temkaa\Container\Service\Definition\Configurator\Argument\TaggedIteratorConfigurator;
use Temkaa\Container\Service\Definition\Configurator\BaseConfigurator;
use Temkaa\Container\Service\Definition\Configurator\DecoratorConfigurator;
use Temkaa\Container\Service\Definition\Configurator\InterfaceConfigurator;
use Temkaa\Container\Service\Definition\ConfiguratorInterface;
use Temkaa\Container\Service\Definition\Instantiator;
use Temkaa\Container\Service\Definition\Populator;
use Temkaa\Container\Service\Definition\Resolver;
use Temkaa\Container\Service\Type\Resolver as TypeResolver;
use Temkaa\Container\Util\Extractor\ClassExtractor;
use Temkaa\Container\Util\Extractor\UniqueDirectoryExtractor;
use function array_merge;
use function realpath;

final class ClassExtractorTest extends TestCase
{
    public static function getDataForExtractTest(): iterable
    {
        $src = __DIR__.'/../../../../src';

        $includedClasses = [
            realpath("$src/Service"),
        ];
        $excludedClasses = [
            realpath("$src/Service/Type"),
            realpath("$src/Service/Compiler.php"),
        ];
        yield [
            array_merge($includedClasses, $excludedClasses),
            $excludedClasses,
            [
                BoundVariableConfigurator::class,
                InstanceConfigurator::class,
                InstanceOfIteratorConfigurator::class,
                ArgumentInterfaceConfigurator::class,
                TaggedIteratorConfigurator::class,
                BaseConfigurator::class,
                DecoratorConfigurator::class,
                InterfaceConfigurator::class,
                ArgumentConfigurator::class,
                Configurator::class,
                ConfiguratorInterface::class,
                Instantiator::class,
                Populator::class,
                Resolver::class,
            ],
            [
                TypeResolver::class,
                Compiler::class,
            ],
        ];
    }

    #[DataProvider('getDataForExtractTest')]
    public function testExtract(
        array $paths,
        array $inputExcludedPaths,
        array $expectedIncludedClasses,
        array $expectedExcludedClasses,
    ): void {
        $uniquePaths = (new UniqueDirectoryExtractor())->extract($paths);

        [$includedClasses, $excludedClasses] = (new ClassExtractor())->extract(
            $uniquePaths,
            $inputExcludedPaths,
        );

        self::assertEqualsCanonicalizing($expectedIncludedClasses, $includedClasses);
        self::assertEqualsCanonicalizing($expectedExcludedClasses, $excludedClasses);
    }
}

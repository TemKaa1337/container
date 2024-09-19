<?php

declare(strict_types=1);

namespace Tests\Benchmark;

use DirectoryIterator;
use PhpBench\Attributes\AfterMethods;
use PhpBench\Attributes\Assert;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Model\Config;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;

/**
 * @psalm-suppress MissingConstructor
 */
final class CompileTimeBenchmark
{
    private const string ATTRIBUTE_AUTOWIRE_SIGNATURE = '#[\Temkaa\Container\Attribute\Autowire(load: %s, singleton: %s)]';
    private const string ATTRIBUTE_DECORATES_SIGNATURE = '#[\Temkaa\Container\Attribute\Decorates(id: %s, priority: %s)]';
    private const string ATTRIBUTE_PARAMETER_SIGNATURE = '#[\Temkaa\Container\Attribute\Bind\Parameter(expression: \'%s\')]';
    private const string ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE = '#[\Temkaa\Container\Attribute\Bind\TaggedIterator(tag: \'%s\')]';
    private const string ATTRIBUTE_TAG_SIGNATURE = '#[\Temkaa\Container\Attribute\Tag(name: \'%s\')]';
    private const string GENERATED_CLASS_ABSOLUTE_NAMESPACE = '\Tests\Fixture\Stub\Class\\';
    private const string GENERATED_CLASS_NAMESPACE = 'Tests\Fixture\Stub\Class\\';
    private const string GENERATED_CLASS_STUB_PATH = '/../Fixture/Stub/Class/';

    private Config $config;

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    #[AfterMethods('tearDown')]
    #[Assert(expression: 'mode(variant.time.avg) < 1.5 milliseconds')]
    #[BeforeMethods('setUp')]
    #[Iterations(20)]
    #[Revs(1000)]
    #[Warmup(revs: 1)]
    public function benchCompilesInSuitableTime(): void
    {
        (new ContainerBuilder())->add($this->config)->build();
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveClassLength)
     */
    public function setUp(): void
    {
        $interfaceName1 = ClassGenerator::getClassName();
        $interfaceName2 = ClassGenerator::getClassName();
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        $className6 = ClassGenerator::getClassName();
        $collectorClassName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$collectorClassName.php")
                    ->setName($collectorClassName)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $dependency1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName1.php")
                    ->setName($interfaceName1)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setHasConstructor(true)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1.'::class',
                            0,
                        ),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_PARAMETER_SIGNATURE,
                            'env(ENV_VAR_1)',
                        ),
                        'public readonly string $arg,',
                        sprintf(
                            'public readonly %s $inner,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1,
                        ),
                        sprintf(self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE, 'Interface2'),
                        'public readonly iterable $dependency,',
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName2.php")
                    ->setName($interfaceName2)
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'Interface2')])
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2])
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName2])
                    ->setAttributes([sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false')]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly int $varOne,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly string $varTwo,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly float $varThree,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_CASTABLE_STRING_VAR)'),
                        'public readonly bool $varFour,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_FLOAT_VAR)'),
                        'public readonly mixed $varFive,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_BOOL_VAL)'),
                        'public readonly mixed $varSix,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_INT_VAL)'),
                        'public readonly mixed $varSeven,',
                        sprintf(self::ATTRIBUTE_PARAMETER_SIGNATURE, 'env(ENV_STRING_VAL)'),
                        'public readonly mixed $varEight,',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className6.php")
                    ->setName($className6),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$collectorClassName.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interfaceName2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className5.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className6.php",
        ];

        $this->config = $this->generateConfig(
            includedPaths: $files,
            interfaceBindings: [
                self::GENERATED_CLASS_NAMESPACE.$interfaceName1 => self::GENERATED_CLASS_NAMESPACE.$className1,
            ],
        );

        $envVariables = [
            'APP_BOUND_VAR'           => 'bound_variable_value',
            'ENV_CASTABLE_STRING_VAR' => '10.1',
            'ENV_FLOAT_VAR'           => '10.1',
            'ENV_BOOL_VAL'            => 'false',
            'ENV_INT_VAL'             => '3',
            'ENV_STRING_VAL'          => 'string',
            'ENV_STRING_VAR'          => 'string',
            'ENV_VAR_1'               => 'test_one',
            'ENV_VAR_2'               => '10.1',
            'ENV_VAR_3'               => 'test-three',
            'ENV_VAR_4'               => 'true',
            'CIRCULAR_ENV_VARIABLE_1' => 'env(CIRCULAR_ENV_VARIABLE_2)',
            'CIRCULAR_ENV_VARIABLE_2' => 'env(CIRCULAR_ENV_VARIABLE_1)',
            'ENV_VARIABLE_REFERENCE'  => 'env(ENV_STRING_VAR)_additional_string',
        ];

        foreach ($envVariables as $name => $value) {
            putenv("$name=$value");
        }
    }

    public function tearDown(): void
    {
        $path = realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH);

        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot() || $file->isDir() || $file->getFilename() === '.gitkeep') {
                continue;
            }

            unlink($file->getRealPath());
        }
    }

    private function generateConfig(
        array $includedPaths = [],
        array $interfaceBindings = [],
    ): Config {
        $builder = new ConfigBuilder();

        foreach ($includedPaths as $path) {
            $builder->include($path);
        }

        foreach ($interfaceBindings as $interface => $class) {
            $builder->bindInterface($interface, $class);
        }

        return $builder->build();
    }
}

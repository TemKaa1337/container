<?php

declare(strict_types=1);

namespace Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Exception\CircularReferenceException;
use Temkaa\Container\Exception\UnresolvableArgumentException;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

/**
 * @psalm-suppress all
 * @SuppressWarnings(PHPMD)
 */
final class GeneralInstanceOfIteratorTest extends AbstractContainerTestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws NotFoundExceptionInterface
     */
    public function testCompilesWithConfigPrecedence(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $interface1 = ClassGenerator::getClassName();
        $interface2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface1.php")
                    ->setName($interface1)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface2.php")
                    ->setName($interface2)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface1]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface2]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_INSTANCE_OF_ITERATOR_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface2.'::class',
                        ),
                        'public readonly array $iterators,',
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interface1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$interface2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];

        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className3,
                    variableBindings: [
                        '$iterators' => new InstanceOfIterator(
                            self::GENERATED_CLASS_NAMESPACE.$interface1,
                        ),
                    ],
                ),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);
        self::assertSame(
            [$container->get(self::GENERATED_CLASS_NAMESPACE.$className1)],
            $class->iterators,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToCircularExceptionByInstanceOfBinding(): void
    {
        $className = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_INSTANCE_OF_ITERATOR_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                        ),
                        'public readonly iterable $arg',
                    ])
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];

        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(CircularReferenceException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate class "%s" as it has circular references "%s".',
                $className,
                $className,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToInstanceOfBindingToNonIterableArgumentType(): void
    {
        $className = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_INSTANCE_OF_ITERATOR_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                        ),
                        'public readonly string $arg',
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('prefix'),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];

        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with instance of iterator argument "arg::string" as it\'s type is neither "array" or "iterable".',
                self::GENERATED_CLASS_NAMESPACE.$className,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToMissingInstanceOfClass(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            self::ATTRIBUTE_INSTANCE_OF_ITERATOR_SIGNATURE,
                            '\'non_existing_interface\'',
                        ),
                        'public readonly array $arg',
                    ]),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];

        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with instance of iterator "non_existing_interface" for argument argument "arg::array" as this class/interface does not exist.',
                self::GENERATED_CLASS_NAMESPACE.$className,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }
}

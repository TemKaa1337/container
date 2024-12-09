<?php

declare(strict_types=1);

namespace Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Container\Attribute\Bind\TaggedIterator;
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
final class GeneralTaggedIteratorTest extends AbstractContainerTestCase
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
                    ->setPrefix('interface')
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'tag_1'),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface2.php")
                    ->setName($interface2)
                    ->setPrefix('interface')
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'tag_2'),
                    ]),
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
                        sprintf(self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE, 'tag_2'),
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
                    variableBindings: ['$iterators' => new TaggedIterator('tag_1')],
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
    public function testDoesNotCompileDueToCircularExceptionByTaggedBinding(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setAttributes([sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'circular')])
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE, 'circular'),
                        'public readonly iterable $arg',
                    ]),
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
    public function testDoesNotCompileDueToTaggedBindingToNonIterableArgumentType(): void
    {
        $className = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE, 'non_iterable_tag'),
                        'public readonly string $arg',
                    ]),
            )
            ->generate();

        $files = [__DIR__.self::GENERATED_CLASS_STUB_PATH."$className.php"];

        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(UnresolvableArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot instantiate entry "%s" with tagged argument "arg::string" as it\'s type is neither "array" or "iterable".',
                self::GENERATED_CLASS_NAMESPACE.$className,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }
}

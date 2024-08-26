<?php

declare(strict_types=1);

namespace Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Temkaa\SimpleContainer\Exception\DuplicatedEntryAliasException;
use Temkaa\SimpleContainer\Exception\EntryNotFoundException;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

/**
 * @psalm-suppress ArgumentTypeCoercion, MixedAssignment
 */
final class AliasTest extends AbstractContainerTestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testAliasNotFound(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_ALIAS_SIGNATURE, 'empty_2'),
                        sprintf(self::ATTRIBUTE_ALIAS_SIGNATURE, 'empty2'),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'private readonly %s $argument,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        self::assertFalse($container->has('alias'));

        $this->expectException(EntryNotFoundException::class);
        $container->get('alias');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithClassAliasesDefinedInConfig(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $interfaceName = ClassGenerator::getClassName();
        $classWithAliasFullNamespace = self::GENERATED_CLASS_NAMESPACE.$className1;
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setInterfaceImplementations([
                        self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_ALIAS_SIGNATURE, 'empty_2'),
                        sprintf(self::ATTRIBUTE_ALIAS_SIGNATURE, 'empty2'),
                    ])
                    ->setConstructorArguments([
                        sprintf(
                            'private readonly %s $argument,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName,
                        ),
                    ]),
            )
            ->generate();

        $config = $this->generateConfig(
            includedPaths: [
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$interfaceName.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className2.'.php',
                __DIR__.self::GENERATED_CLASS_STUB_PATH.$className1.'.php',
            ],
            classBindings: [
                $this->generateClassConfig($classWithAliasFullNamespace, aliases: ['alias_from_config']),
            ],
        );

        $container = (new ContainerBuilder())->add($config)->build();

        $class = $container->get($classWithAliasFullNamespace);
        self::assertInstanceOf($classWithAliasFullNamespace, $class);
        self::assertTrue($container->has('empty_2'));
        self::assertTrue($container->has('empty2'));
        self::assertTrue($container->has('alias_from_config'));
        self::assertSame($class, $container->get('empty_2'));
        self::assertSame($class, $container->get('empty2'));
        self::assertSame($class, $container->get('alias_from_config'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToDuplicatedAliasesFromAttributes(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_ALIAS_SIGNATURE, 'NonUniqueAlias'),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_ALIAS_SIGNATURE, 'NonUniqueAlias'),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
        ];

        $config = $this->generateConfig(includedPaths: $files);

        $this->expectException(DuplicatedEntryAliasException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not compile container as there are duplicated alias "NonUniqueAlias" in class "%s", found in "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className2,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToDuplicatedAliasesFromConfig(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
        ];

        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className1,
                    aliases: ['non_unique_alias'],
                ),
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className2,
                    aliases: ['non_unique_alias'],
                ),
            ],
        );

        $this->expectException(DuplicatedEntryAliasException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not compile container as there are duplicated alias "non_unique_alias" in class "%s", found in "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className2,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testDoesNotCompileDueToDuplicatedAliasesFromConfigAndAttributes(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_ALIAS_SIGNATURE, 'non_unique_alias'),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
        ];

        $config = $this->generateConfig(
            includedPaths: $files,
            classBindings: [
                $this->generateClassConfig(
                    className: self::GENERATED_CLASS_NAMESPACE.$className2,
                    aliases: ['non_unique_alias'],
                ),
            ],
        );

        $this->expectException(DuplicatedEntryAliasException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not compile container as there are duplicated alias "non_unique_alias" in class "%s", found in "%s".',
                self::GENERATED_CLASS_NAMESPACE.$className2,
                self::GENERATED_CLASS_NAMESPACE.$className1,
            ),
        );

        (new ContainerBuilder())->add($config)->build();
    }
}

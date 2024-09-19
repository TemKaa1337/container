<?php

declare(strict_types=1);

namespace Container\Config;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Container\Builder\ContainerBuilder;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

/**
 * @psalm-suppress ArgumentTypeCoercion, MixedPropertyFetch, MixedAssignment
 */
final class AliasTest extends AbstractContainerTestCase
{
    /**
     * @psalm-suppress InvalidClassConstantType
     */
    protected const string GENERATED_CLASS_STUB_PATH = '/../../../Fixture/Stub/Class/';

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
}

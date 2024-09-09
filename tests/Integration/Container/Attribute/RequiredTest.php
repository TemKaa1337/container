<?php

declare(strict_types=1);

namespace Container\Attribute;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Builder\ContainerBuilder;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\Container\AbstractContainerTestCase;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 *
 * @psalm-suppress ArgumentTypeCoercion, MixedPropertyFetch, MixedAssignment, MixedArgument
 */
final class RequiredTest extends AbstractContainerTestCase
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
    public function testCompilesWhenClassIsDecorator(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                        ),
                    ])
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            2,
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$interface);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $object);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $object);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $object->arg1);
        $this->assertInitialized($object->arg1, 'arg1');
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $object->arg1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $object->arg1->arg1->arg1);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWhenClassIsDecoratorAndNonSingleton(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                        ),
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                        ),
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            2,
                        ),
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $objectVersion1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$interface);
        $objectVersion2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$interface);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $objectVersion1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $objectVersion1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $objectVersion1->arg1);
        $this->assertInitialized($objectVersion1->arg1, 'arg1');
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $objectVersion1->arg1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $objectVersion1->arg1->arg1->arg1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $objectVersion2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $objectVersion2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $objectVersion2->arg1);
        $this->assertInitialized($objectVersion2->arg1, 'arg1');
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $objectVersion2->arg1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $objectVersion2->arg1->arg1->arg1);

        self::assertNotSame($objectVersion1, $objectVersion2);
        self::assertNotSame($objectVersion1->arg1, $objectVersion2->arg1);
        self::assertNotSame($objectVersion1->arg1->arg1, $objectVersion2->arg1->arg1);
        self::assertNotSame($objectVersion1->arg1->arg1->arg1, $objectVersion2->arg1->arg1->arg1);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithBindingDecorator(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        $this->assertInitialized($object, 'arg1');

        $decorator = $container->get(self::GENERATED_CLASS_NAMESPACE.$interface);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $decorator);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $decorator);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $decorator->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $decorator->arg1->arg1);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $object->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $object->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $object->arg1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $object->arg1->arg1->arg1);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDecoratorSignatureInMultipleMethods(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                        ),
                    ])
                    ->setBody([
                        sprintf('public readonly %s $decorator1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        sprintf('public readonly %s $decorator2;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        sprintf('public readonly %s $decorator3;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        sprintf('public readonly %s $decorator4;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        'public readonly string $stringVar;',
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $decorator1): void
                            {
                                $this->decorator1 = $decorator1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg2(%s string $arg2, %s $inner): void
                            {
                                $this->stringVar = $arg2;
                                $this->decorator2 = $inner;
                            }
                            METHOD,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(APP_BOUND_VAR)'),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg3(%s string $arg1, %s int $arg2, %s $inner): void
                            {
                                $this->decorator3 = $inner;
                            }
                            METHOD,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(APP_BOUND_VAR)'),
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_FLOAT_VAR)'),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg4(%s string $arg1, %s int $arg2, %s $inner): void
                            {
                                $this->decorator4 = $inner;
                            }
                            METHOD,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(APP_BOUND_VAR)'),
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'env(ENV_FLOAT_VAR)'),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            2,
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $class1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$interface);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $class1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $class1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class1->arg1->decorator1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $class1->arg1->decorator1->arg1);

        $this->assertInitialized($class1->arg1, 'decorator1');
        $this->assertInitialized($class1->arg1, 'decorator2');
        $this->assertInitialized($class1->arg1, 'decorator3');
        $this->assertInitialized($class1->arg1, 'decorator4');
        $this->assertInitialized($class1->arg1, 'stringVar');

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class1->arg1->decorator1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class1->arg1->decorator2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class1->arg1->decorator3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $class1->arg1->decorator4);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class1->arg1->decorator1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class1->arg1->decorator2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class1->arg1->decorator3);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $class1->arg1->decorator4);
        self::assertEquals('bound_variable_value', $class1->arg1->stringVar);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCompilesWithDecoratorWithMultipleRequiredMethods(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            1,
                        ),
                    ])
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        sprintf('public readonly %s $arg2;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        <<<'METHOD'
                            public function empty(): void
                            {
                            }
                        METHOD,
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setString(%s string $arg1): void
                            {
                            
                            }
                            METHOD,
                            sprintf(self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE, 'string'),
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setDecorator1(%s $asd): void
                            {
                                $this->arg1 = $asd;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setDecorator2(%s $decorator, string $asd = 'asd'): void
                            {
                                $this->arg2 = $decorator;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            0,
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(
                            self::ATTRIBUTE_DECORATES_SIGNATURE,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            2,
                        ),
                    ])
                    ->setHasConstructor(true)
                    ->setConstructorArguments([
                        sprintf(
                            'public readonly %s $arg1,',
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface,
                        ),
                    ])
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$interface);

        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$interface, $object);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className2, $object);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className1, $object->arg1);
        $this->assertInitialized($object->arg1, 'arg1');
        $this->assertInitialized($object->arg1, 'arg2');
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $object->arg1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className3, $object->arg1->arg2);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $object->arg1->arg1->arg1);
        self::assertInstanceOf(self::GENERATED_CLASS_NAMESPACE.$className4, $object->arg1->arg2->arg1);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCompilesWithMultipleRequiredMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg2(%s $arg2): void
                            {
                                $this->arg2 = $arg2;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        $this->assertInitialized($object, 'arg1');
        $this->assertInitialized($object, 'arg2');

        self::assertSame($object->arg1, $container->get(self::GENERATED_CLASS_NAMESPACE.$className2));
        self::assertSame($object->arg2, $container->get(self::GENERATED_CLASS_NAMESPACE.$className3));
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCompilesWithMultipleRequiredMethodWithArgumentsBind(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $className4 = ClassGenerator::getClassName();
        $className5 = ClassGenerator::getClassName();
        $interface = ClassGenerator::getClassName();
        $enum = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([
                        'public readonly string $arg1;',
                        'public readonly array $arg2;',
                        sprintf('public readonly %s $arg3;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$enum),
                        'public readonly array $arg4;',
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s string $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            sprintf(
                                self::ATTRIBUTE_PARAMETER_STRING_SIGNATURE,
                                'env(APP_BOUND_VAR)',
                            ),
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg2(%s array $arg2): void
                            {
                                $this->arg2 = $arg2;
                            }
                            METHOD,
                            sprintf(
                                self::ATTRIBUTE_TAGGED_ITERATOR_SIGNATURE,
                                'tag',
                            ),
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg3(%s %s $arg3): void
                            {
                                $this->arg3 = $arg3;
                            }
                            METHOD,
                            sprintf(
                                self::ATTRIBUTE_PARAMETER_RAW_SIGNATURE,
                                self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$enum.'::EnumCaseOne',
                            ),
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$enum,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg4(%s array $arg4): void
                            {
                                $this->arg4 = $arg4;
                            }
                            METHOD,
                            sprintf(
                                self::ATTRIBUTE_INSTANCE_OF_ITERATOR_SIGNATURE,
                                self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface.'::class',
                            ),
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'tag'),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_TAG_SIGNATURE, 'tag'),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className4.php")
                    ->setName($className4)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className5.php")
                    ->setName($className5)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interface]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interface.php")
                    ->setName($interface)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$enum.php")
                    ->setName($enum)
                    ->setPrefix('enum')
                    ->setBody([
                        'case EnumCaseOne;',
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className4.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className5.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        $this->assertInitialized($object, 'arg1');
        $this->assertInitialized($object, 'arg2');
        $this->assertInitialized($object, 'arg3');
        $this->assertInitialized($object, 'arg4');

        self::assertEquals('bound_variable_value', $object->arg1);
        self::assertSame(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className2),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className3),
            ],
            $object->arg2,
        );
        self::assertSame(constant(self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$enum.'::EnumCaseOne'), $object->arg3);

        self::assertSame(
            [
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className4),
                $container->get(self::GENERATED_CLASS_NAMESPACE.$className5),
            ],
            $object->arg4,
        );
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCompilesWithNonSingletonClassAndMultipleRequiredMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg2(%s $arg2): void
                            {
                                $this->arg2 = $arg2;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $objectVersion1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $objectVersion2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);

        $this->assertInitialized($objectVersion1, 'arg1');
        $this->assertInitialized($objectVersion1, 'arg2');
        $this->assertInitialized($objectVersion2, 'arg1');
        $this->assertInitialized($objectVersion2, 'arg2');

        self::assertNotSame($objectVersion1, $objectVersion2);
        self::assertSame($objectVersion1->arg1, $class2);
        self::assertSame($objectVersion1->arg2, $class3);
        self::assertSame($objectVersion2->arg1, $class2);
        self::assertSame($objectVersion2->arg2, $class3);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCompilesWithNonSingletonClassAndMultipleRequiredMethodWhichBoundNonSingletonClasses(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();

        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ])
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg2(%s $arg2): void
                            {
                                $this->arg2 = $arg2;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3)
                    ->setAttributes([
                        sprintf(self::ATTRIBUTE_AUTOWIRE_SIGNATURE, 'true', 'false'),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $objectVersion1 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $objectVersion2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);
        $class2 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className2);
        $class3 = $container->get(self::GENERATED_CLASS_NAMESPACE.$className3);

        $this->assertInitialized($objectVersion1, 'arg1');
        $this->assertInitialized($objectVersion1, 'arg2');
        $this->assertInitialized($objectVersion2, 'arg1');
        $this->assertInitialized($objectVersion2, 'arg2');

        self::assertNotSame($objectVersion1, $objectVersion2);
        self::assertInstanceOf($class2::class, $objectVersion1->arg1);
        self::assertInstanceOf($class3::class, $objectVersion1->arg2);
        self::assertInstanceOf($class2::class, $objectVersion2->arg1);
        self::assertInstanceOf($class3::class, $objectVersion2->arg2);
        self::assertNotSame($objectVersion1->arg1, $objectVersion2->arg1);
        self::assertNotSame($objectVersion1->arg2, $objectVersion2->arg2);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCompilesWithTraitWithMultipleRequiredMethod(): void
    {
        $className1 = ClassGenerator::getClassName();
        $className2 = ClassGenerator::getClassName();
        $className3 = ClassGenerator::getClassName();
        $trait = ClassGenerator::getClassName();
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className1.php")
                    ->setName($className1)
                    ->setBody([sprintf('use %s;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$trait)]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className2.php")
                    ->setName($className2),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className3.php")
                    ->setName($className3),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$trait.php")
                    ->setName($trait)
                    ->setPrefix('trait')
                    ->setBody([
                        sprintf('public readonly %s $arg1;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2),
                        sprintf('public readonly %s $arg2;', self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg1(%s $arg1): void
                            {
                                $this->arg1 = $arg1;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className2,
                        ),
                        self::ATTRIBUTE_REQUIRED_SIGNATURE,
                        sprintf(
                            <<<'METHOD'
                            public function setArg2(%s $arg2): void
                            {
                                $this->arg2 = $arg2;
                            }
                            METHOD,
                            self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$className3,
                        ),
                    ]),
            )
            ->generate();

        $files = [
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className1.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className2.php",
            __DIR__.self::GENERATED_CLASS_STUB_PATH."$className3.php",
        ];
        $config = $this->generateConfig(includedPaths: $files);

        $container = (new ContainerBuilder())->add($config)->build();

        $object = $container->get(self::GENERATED_CLASS_NAMESPACE.$className1);

        $this->assertInitialized($object, 'arg1');
        $this->assertInitialized($object, 'arg2');

        self::assertSame($object->arg1, $container->get(self::GENERATED_CLASS_NAMESPACE.$className2));
        self::assertSame($object->arg2, $container->get(self::GENERATED_CLASS_NAMESPACE.$className3));
    }

    /**
     * @throws ReflectionException
     */
    private function assertInitialized(object $class, string $propertyName): void
    {
        $r = new ReflectionClass($class);

        $property = $r->getProperty($propertyName);
        if (!$property->isInitialized($class)) {
            self::fail(sprintf('Failed asserting property "%s" is initialized.', $propertyName));
        }
    }
}

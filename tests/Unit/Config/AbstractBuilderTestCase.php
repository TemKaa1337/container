<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\CannotBindInterfaceException;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Unit\AbstractUnitTestCase;

abstract class AbstractBuilderTestCase extends AbstractUnitTestCase
{
    protected const GENERATED_CLASS_STUB_PATH = '/../../Fixture/Stub/Class/';
    protected const GENERATED_CONFIG_STUB_PATH = '/../../Fixture/Stub/Config/';

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function getDataForIncorrectConfigNodeTypesTest(): iterable
    {
        $invalidTypes = [
            10,
            10.1,
            true,
            '',
        ];

        foreach ($invalidTypes as $invalidType) {
            $services = $invalidType;

            yield [
                $services,
                [],
                [],
                InvalidConfigNodeTypeException::class,
                'Node "services" must be of "array<include|exclude, array>" type.',
            ];
        }

        $invalidTypes = [
            10,
            10.1,
            true,
            '',
        ];

        foreach (['include', 'exclude'] as $key) {
            foreach ($invalidTypes as $invalidType) {
                $services = [$key => $invalidType];

                yield [
                    $services,
                    [],
                    [],
                    InvalidConfigNodeTypeException::class,
                    sprintf('services.%s" must be of "array<int, array>" type.', $key),
                ];
            }
        }

        foreach ($invalidTypes as $invalidType) {
            $interfaceBindings = $invalidType;

            yield [
                [],
                $interfaceBindings,
                [],
                InvalidConfigNodeTypeException::class,
                'Node "interface_bindings" must be of "array<string, string>" type.',
            ];
        }

        $invalidTypes = [
            10,
            10.1,
            true,
        ];

        foreach ($invalidTypes as $invalidType) {
            /** @psalm-suppress InvalidArrayOffset */
            $interfaceBindings = [$invalidType => $invalidType];

            yield [
                [],
                $interfaceBindings,
                [],
                InvalidConfigNodeTypeException::class,
                'Node "interface_bindings" must be of "array<string, string>" type.',
            ];
        }

        $invalidTypes = [
            10,
            10.1,
            true,
        ];

        foreach ($invalidTypes as $invalidType) {
            $interfaceBindings = ['interface' => $invalidType];

            yield [
                [],
                $interfaceBindings,
                [],
                InvalidConfigNodeTypeException::class,
                'Node "interface_bindings" must be of "array<string, string>" type.',
            ];
        }

        $invalidTypes = [
            10,
            10.1,
            true,
        ];

        foreach ($invalidTypes as $invalidType) {
            $classBindings = $invalidType;

            yield [
                [],
                [],
                $classBindings,
                InvalidConfigNodeTypeException::class,
                'Node "class_bindings" must be of "array<string, array>" type.',
            ];
        }

        $emptyClassName = ClassGenerator::getClassName();
        $emptyClassNamespace = self::GENERATED_CLASS_NAMESPACE.$emptyClassName;
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$emptyClassName.php")
                    ->setName($emptyClassName),
            )
            ->generate();

        foreach ($invalidTypes as $invalidType) {
            $classBindings = [$emptyClassNamespace => $invalidType];

            yield [
                [],
                [],
                $classBindings,
                InvalidConfigNodeTypeException::class,
                'Node "class_bindings" must be of "array<string, array>" type.',
            ];
        }

        foreach ($invalidTypes as $invalidType) {
            $classBindings = [$emptyClassNamespace => ['bind' => $invalidType]];

            yield [
                [],
                [],
                $classBindings,
                InvalidConfigNodeTypeException::class,
                'Node "class_bindings.{className}.bind" must be of "array<string, string>" type.',
            ];
        }

        $invalidTypes[] = ['key' => 'value'];

        foreach ($invalidTypes as $invalidType) {
            $classBindings = [$emptyClassNamespace => ['tags' => $invalidType]];

            yield [
                [],
                [],
                $classBindings,
                InvalidConfigNodeTypeException::class,
                'Node "class_bindings.{className}.tags" must be of "array<int, string>" type.',
            ];
        }

        foreach ($invalidTypes as $invalidType) {
            $classBindings = [$emptyClassNamespace => ['tags' => [$invalidType]]];

            yield [
                [],
                [],
                $classBindings,
                InvalidConfigNodeTypeException::class,
                'Node "class_bindings.{className}.tags" must be of "array<int, string>" type.',
            ];
        }
    }

    public static function getDataForInterfaceBindingErrorsTest(): iterable
    {
        $className = ClassGenerator::getClassName();
        $classFullNamespace = self::GENERATED_CLASS_NAMESPACE.$className;
        $interfaceName = ClassGenerator::getClassName();
        $interfaceFullNamespace = self::GENERATED_CLASS_NAMESPACE.$interfaceName;
        $emptyClassName = ClassGenerator::getClassName();
        $emptyClassNamespace = self::GENERATED_CLASS_NAMESPACE.$emptyClassName;
        (new ClassGenerator())
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$className.php")
                    ->setName($className)
                    ->setInterfaceImplementations([self::GENERATED_CLASS_ABSOLUTE_NAMESPACE.$interfaceName]),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$interfaceName.php")
                    ->setName($interfaceName)
                    ->setPrefix('interface'),
            )
            ->addBuilder(
                (new ClassBuilder())
                    ->setAbsolutePath(realpath(__DIR__.self::GENERATED_CLASS_STUB_PATH)."/$emptyClassName.php")
                    ->setName($emptyClassName),
            )
            ->generate();

        yield [
            ['NonExistentInterface' => $classFullNamespace],
            ClassNotFoundException::class,
            sprintf('Class "%s" is not found.', 'NonExistentInterface'),
        ];

        yield [
            [$interfaceFullNamespace => 'NonExistentInterfaceImplementation'],
            ClassNotFoundException::class,
            sprintf('Class "%s" is not found.', 'NonExistentInterfaceImplementation'),
        ];

        yield [
            [$interfaceFullNamespace => $emptyClassNamespace],
            CannotBindInterfaceException::class,
            sprintf(
                'Cannot bind interface "%s" to class "%s" as it doesn\'t implement it.',
                $interfaceFullNamespace,
                $emptyClassNamespace,
            ),
        ];
    }
}
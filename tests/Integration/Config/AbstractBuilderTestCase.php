<?php

declare(strict_types=1);

namespace Tests\Integration\Config;

use Temkaa\SimpleContainer\Enum\Config\Structure;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\CannotBindInterfaceException;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;
use Tests\Helper\Service\ClassBuilder;
use Tests\Helper\Service\ClassGenerator;
use Tests\Integration\AbstractUnitTestCase;

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
                [],
                InvalidConfigNodeTypeException::class,
                'Node "services" must be of "array<string, array|string>" type.',
            ];
        }

        $invalidTypes = [
            10,
            10.1,
            true,
            '',
        ];

        foreach ([Structure::Include->value, Structure::Exclude->value] as $key) {
            foreach ($invalidTypes as $invalidType) {
                $services = [$key => $invalidType];

                yield [
                    $services,
                    [],
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
                [],
                $interfaceBindings,
                [],
                InvalidConfigNodeTypeException::class,
                'Node "services" must be of "array<string, array|string>" type.',
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
                [],
                $interfaceBindings,
                [],
                InvalidConfigNodeTypeException::class,
                'Node "services.{className|interfaceName}" must be of "array<string, array|string>" type.',
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
                [],
                $interfaceBindings,
                [],
                ClassNotFoundException::class,
                'Class "interface" is not found.',
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
                [],
                $classBindings,
                InvalidConfigNodeTypeException::class,
                'Node "services" must be of "array<string, array|string>" type.',
            ];
        }

        foreach ($invalidTypes as $invalidType) {
            $globalBoundVariables = $invalidType;

            yield [
                [],
                $globalBoundVariables,
                [],
                [],
                InvalidConfigNodeTypeException::class,
                'Node "services.bind" must be of "array<string, string>" type.',
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
                [],
                $classBindings,
                InvalidConfigNodeTypeException::class,
                sprintf(
                    'Node "services.%s" must be of "array<string, array<string, array>>" type.',
                    $emptyClassNamespace,
                ),
            ];
        }

        foreach ($invalidTypes as $invalidType) {
            $classBindings = [$emptyClassNamespace => [Structure::Bind->value => $invalidType]];

            yield [
                [],
                [],
                [],
                $classBindings,
                InvalidConfigNodeTypeException::class,
                'Node "services.{className}.bind" must be of "array<string, string>" type.',
            ];
        }

        $invalidTypes[] = ['key' => 'value'];

        foreach ($invalidTypes as $invalidType) {
            $classBindings = [$emptyClassNamespace => [Structure::Tags->value => $invalidType]];

            yield [
                [],
                [],
                [],
                $classBindings,
                InvalidConfigNodeTypeException::class,
                'Node "services.{className}.tags" must be of "list<string>" type.',
            ];
        }

        foreach ($invalidTypes as $invalidType) {
            $classBindings = [$emptyClassNamespace => [Structure::Tags->value => [$invalidType]]];

            yield [
                [],
                [],
                [],
                $classBindings,
                InvalidConfigNodeTypeException::class,
                'Node "services.{className}.tags" must be of "list<string>" type.',
            ];
        }

        foreach ($invalidTypes as $invalidType) {
            $classBindings = [$emptyClassNamespace => [Structure::Decorates->value => [$invalidType]]];

            yield [
                [],
                [],
                [],
                $classBindings,
                InvalidConfigNodeTypeException::class,
                'Node "services.{className}.decorates" must be of "array<string, string|int>" type.',
            ];
        }

        yield [
            [],
            [],
            [],
            [$emptyClassNamespace => [Structure::Decorates->value => [Structure::Id->value => 'non_existent_class']]],
            ClassNotFoundException::class,
            'Class "non_existent_class" is not found.',
        ];
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

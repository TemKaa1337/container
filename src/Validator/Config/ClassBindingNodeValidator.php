<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Temkaa\SimpleContainer\Enum\Config\Structure;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Util\ExpressionParser;

/**
 * @internal
 */
final class ClassBindingNodeValidator implements ValidatorInterface
{
    // TODO: add validations on this
    private const ALLOWED_CLASS_INFO_STRUCTURE_NODE_NAMES = [
        Structure::Bind,
        Structure::Tags,
        Structure::Decorates,
        Structure::Singleton,
    ];
    private const ALLOWED_DECORATOR_STRUCTURE_NODE_NAMES = [Structure::Id, Structure::Priority, Structure::Signature];

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @throws ReflectionException
     */
    public function validate(array $config): void
    {
        foreach ($config[Structure::Services->value] ?? [] as $nodeName => $nodeValue) {
            if (!is_string($nodeName)) {
                throw new InvalidConfigNodeTypeException(
                    'Node "services.{className|interfaceName}" must be of "array<string, array|string>" type.',
                );
            }

            if (Structure::tryFrom($nodeName)) {
                continue;
            }

            if (!class_exists($nodeName) && !interface_exists($nodeName)) {
                throw new ClassNotFoundException($nodeName);
            }

            if (interface_exists($nodeName)) {
                continue;
            }

            if (!class_exists($nodeName)) {
                throw new ClassNotFoundException($nodeName);
            }

            if (!is_array($nodeValue) || array_is_list($nodeValue)) {
                throw new InvalidConfigNodeTypeException(
                    sprintf('Node "services.%s" must be of "array<string, array<string, array>>" type.', $nodeName),
                );
            }

            if (isset($nodeValue[Structure::Bind->value])) {
                $this->validateBoundVariables($nodeValue);
            }

            if (isset($nodeValue[Structure::Tags->value])) {
                $this->validateTags($nodeValue);
            }

            if (isset($nodeValue[Structure::Singleton->value])) {
                $this->validateSingleton($nodeValue);
            }

            if (isset($nodeValue[Structure::Decorates->value])) {
                $this->validateDecorator($nodeName, $nodeValue);
            }
        }
    }

    private function validateBoundVariables(array $classInfo): void
    {
        if (!is_array($classInfo[Structure::Bind->value])) {
            throw new InvalidConfigNodeTypeException(
                'Node "services.{className}.bind" must be of "array<string, string>" type.',
            );
        }

        $expressionParser = new ExpressionParser();
        foreach ($classInfo[Structure::Bind->value] as $variableName => $variableValue) {
            if (!is_string($variableName) || !is_string($variableValue)) {
                throw new InvalidConfigNodeTypeException(
                    'Node "services.{className}.bind" must be of "array<string, string>" type.',
                );
            }

            $expressionParser->parse($variableValue);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param class-string $className
     *
     * @throws ReflectionException
     */
    private function validateDecorator(string $className, array $classInfo): void
    {
        if (
            !is_array($classInfo[Structure::Decorates->value])
            || array_is_list($classInfo[Structure::Decorates->value])
        ) {
            throw new InvalidConfigNodeTypeException(
                'Node "services.{className}.decorates" must be of "array<string, string|int>" type.',
            );
        }

        foreach (array_keys($classInfo[Structure::Decorates->value]) as $structureNode) {
            if (!is_string($structureNode)) {
                throw new InvalidConfigNodeTypeException(
                    'Node "services.{className}.decorates" must be of "array<string, string|int>" type.',
                );
            }

            if (
                !in_array(
                    Structure::tryFrom($structureNode),
                    self::ALLOWED_DECORATOR_STRUCTURE_NODE_NAMES,
                    strict: true,
                )
            ) {
                throw new InvalidConfigNodeTypeException(
                    sprintf(
                        'Node "services.{className}.decorates" allows having only "%s" as keys.',
                        implode(
                            '|',
                            array_map(
                                static fn (Structure $node): string => $node->value,
                                self::ALLOWED_DECORATOR_STRUCTURE_NODE_NAMES,
                            ),
                        ),
                    ),
                );
            }
        }

        foreach (self::ALLOWED_DECORATOR_STRUCTURE_NODE_NAMES as $node) {
            if (!isset($classInfo[Structure::Decorates->value][$node->value])) {
                continue;
            }

            $value = $classInfo[Structure::Decorates->value][$node->value];
            switch ($node) {
                case Structure::Id:
                    if (!is_string($value)) {
                        throw new InvalidConfigNodeTypeException(
                            'Node "services.{className}.decorates.id" must be of "string" type.',
                        );
                    }

                    if (!class_exists($value) && !interface_exists($value)) {
                        throw new ClassNotFoundException($value);
                    }

                    break;
                case Structure::Signature:
                    if (!is_string($value)) {
                        throw new InvalidConfigNodeTypeException(
                            'Node "services.{className}.decorates.signature" must be of "string" type.',
                        );
                    }

                    $reflection = new ReflectionClass($className);

                    $constructorArguments = $reflection->getConstructor()?->getParameters() ?? [];
                    $argumentNames = array_map(
                        static fn (ReflectionParameter $argument): string => $argument->getName(),
                        $constructorArguments,
                    );

                    if (!in_array(str_replace('$', '', $value), $argumentNames, strict: true)) {
                        throw new UnresolvableArgumentException(
                            sprintf(
                                'Could not resolve decorated class in class "%s" as it does not have argument named "%s".',
                                $className,
                                $value,
                            ),
                        );
                    }

                    break;
                case Structure::Priority:
                    if (!is_int($value)) {
                        throw new InvalidConfigNodeTypeException(
                            'Node "services.{className}.decorates.priority" must be of "int" type.',
                        );
                    }

                    break;
            }
        }
    }

    private function validateSingleton(array $classInfo): void
    {
        if (!is_bool($classInfo[Structure::Singleton->value])) {
            throw new InvalidConfigNodeTypeException(
                'Node "services.{className}.singleton" must be of "bool" type.',
            );
        }
    }

    private function validateTags(array $classInfo): void
    {
        if (!is_array($classInfo[Structure::Tags->value]) || !array_is_list($classInfo[Structure::Tags->value])) {
            throw new InvalidConfigNodeTypeException(
                'Node "services.{className}.tags" must be of "list<string>" type.',
            );
        }

        foreach ($classInfo[Structure::Tags->value] as $tag) {
            if (!is_string($tag)) {
                throw new InvalidConfigNodeTypeException(
                    'Node "services.{className}.tags" must be of "list<string>" type.',
                );
            }
        }
    }
}

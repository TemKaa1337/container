<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Temkaa\SimpleContainer\Enum\Config\Structure;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;
use Temkaa\SimpleContainer\Util\ExpressionParser;

final class ClassBindingNodeValidator implements ValidatorInterface
{
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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

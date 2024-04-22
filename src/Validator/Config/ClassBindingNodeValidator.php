<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Psr\Container\ContainerExceptionInterface;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;
use Temkaa\SimpleContainer\Exception\EnvVariableNotFoundException;
use Temkaa\SimpleContainer\Util\Env;

final class ClassBindingNodeValidator implements ValidatorInterface
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function validate(array $config): void
    {
        if (!isset($config['class_bindings'])) {
            return;
        }

        if (!is_array($config['class_bindings'])) {
            throw new InvalidConfigNodeTypeException(
                'Node "class_bindings" must be of "array<string, array>" type.',
            );
        }

        foreach ($config['class_bindings'] as $className => $classInfo) {
            if (!is_string($className) || !is_array($classInfo)) {
                throw new InvalidConfigNodeTypeException(
                    'Node "class_bindings" must be of "array<string, array>" type.',
                );
            }

            if (!class_exists($className)) {
                throw new ClassNotFoundException($className);
            }

            if (isset($classInfo['bind'])) {
                $this->validateBoundVariables($classInfo);
            }

            if (isset($classInfo['tags'])) {
                $this->validateTags($classInfo);
            }
        }
    }

    private function validateBoundVariables(array $classInfo): void
    {
        if (!is_array($classInfo['bind'])) {
            throw new InvalidConfigNodeTypeException(
                'Node "class_bindings.{className}.bind" must be of "array<string, string>" type.',
            );
        }

        foreach ($classInfo['bind'] as $variableName => $variableValue) {
            if (!is_string($variableName) || !is_string($variableValue)) {
                throw new InvalidConfigNodeTypeException(
                    'Node "class_bindings.{className}.bind" must be of "array<string, string>" type.',
                );
            }

            $matches = [];
            preg_match_all('#env\((.*?)\)#', $variableValue, matches: $matches);

            $envVarsBindings = $matches[1] ?? [];
            if ($envVarsBindings) {
                foreach ($envVarsBindings as $binding) {
                    if (!Env::has($binding)) {
                        throw new EnvVariableNotFoundException(
                            sprintf('Variable "%s" is not found in dov env variables.', $binding),
                        );
                    }
                }
            }
        }
    }

    private function validateTags(array $classInfo): void
    {
        if (!is_array($classInfo['tags'])) {
            throw new InvalidConfigNodeTypeException(
                'Node "class_bindings.{className}.tags" must be of "array<int, string>" type.',
            );
        }

        if (!array_is_list($classInfo['tags'])) {
            throw new InvalidConfigNodeTypeException(
                'Node "class_bindings.{className}.tags" must be of "array<int, string>" type.',
            );
        }

        foreach ($classInfo['tags'] as $tag) {
            if (!is_string($tag)) {
                throw new InvalidConfigNodeTypeException(
                    'Node "class_bindings.{className}.tags" must be of "array<int, string>" type.',
                );
            }
        }
    }
}

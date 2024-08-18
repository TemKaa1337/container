<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Model\Container\ClassConfig;
use Temkaa\SimpleContainer\Model\Container\ConfigNew;
use Temkaa\SimpleContainer\Util\ExpressionParser;

/**
 * @internal
 */
final class ClassBindingNodeValidator implements ValidatorInterface
{
    /**
     * @throws ReflectionException
     */
    public function validate(ConfigNew $config): void
    {
        foreach ($config->getBoundedClasses() as $classConfig) {
            if (!class_exists($classConfig->getClass())) {
                throw new ClassNotFoundException($classConfig->getClass());
            }

            $this->validateBoundVariables($classConfig->getBoundVariables());
            $this->validateDecorator($classConfig);
        }
    }

    private function validateBoundVariables(array $boundVariables): void
    {
        // TODO: change from parsing to validation
        $expressionParser = new ExpressionParser();
        foreach ($boundVariables as $variableValue) {
            $expressionParser->parse($variableValue);
        }
    }

    /**
     * @throws ReflectionException
     */
    private function validateDecorator(ClassConfig $classConfig): void
    {
        if (!$decorator = $classConfig->getDecorates()) {
            return;
        }

        if (!class_exists($decorator->getId()) && !interface_exists($decorator->getId())) {
            throw new ClassNotFoundException($decorator->getId());
        }

        $reflection = new ReflectionClass($classConfig->getClass());

        $constructorArguments = $reflection->getConstructor()?->getParameters() ?? [];
        $argumentNames = array_map(
            static fn (ReflectionParameter $argument): string => $argument->getName(),
            $constructorArguments,
        );

        if (!in_array(str_replace('$', '', $decorator->getSignature()), $argumentNames, strict: true)) {
            throw new UnresolvableArgumentException(
                sprintf(
                    'Could not resolve decorated class in class "%s" as it does not have argument named "%s".',
                    $classConfig->getClass(),
                    $decorator->getSignature(),
                ),
            );
        }
    }
}

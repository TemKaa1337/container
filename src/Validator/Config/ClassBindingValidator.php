<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Config\ClassConfig;

/**
 * @internal
 */
final class ClassBindingValidator implements ValidatorInterface
{
    /**
     * @throws ReflectionException
     */
    public function validate(Config $config): void
    {
        foreach ($config->getBoundedClasses() as $classConfig) {
            if (!class_exists($classConfig->getClass())) {
                throw new ClassNotFoundException($classConfig->getClass());
            }

            $this->validateDecorator($classConfig);
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

        if (
            count($constructorArguments) !== 1
            && !in_array(str_replace('$', '', $decorator->getSignature()), $argumentNames, strict: true)
        ) {
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

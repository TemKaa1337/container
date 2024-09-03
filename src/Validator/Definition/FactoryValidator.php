<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Definition;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use Temkaa\SimpleContainer\Exception\ClassFactoryException;
use Temkaa\SimpleContainer\Exception\Config\EntryNotFoundException;
use Temkaa\SimpleContainer\Model\Config\Factory;

/**
 * @internal
 */
final class FactoryValidator
{
    /**
     * @param Factory      $factory
     * @param class-string $id
     *
     * @throws ReflectionException
     */
    public function validate(Factory $factory, string $id): void
    {
        if (!class_exists($factory->getId())) {
            throw new EntryNotFoundException(
                sprintf('Class "%s" not found.', $factory->getId()),
            );
        }

        $factoryReflection = new ReflectionClass($factory->getId());
        $this->validateFactoryClass($factoryReflection, $factory, $id);

        $this->validateRootClass($factoryReflection, $factory, $id);
    }

    /**
     * @throws ReflectionException
     */
    private function validateFactoryClass(ReflectionClass $factoryReflection, Factory $factory, string $id): void
    {
        if ($factoryReflection->isInternal()) {
            throw new ClassFactoryException(
                sprintf(
                    'Factory method "%s::%s" for class "%s" cannot not be internal.',
                    $factory->getMethod(),
                    $factoryReflection->getName(),
                    $id,
                ),
            );
        }

        if (!$factoryReflection->hasMethod($factory->getMethod())) {
            throw new ClassFactoryException(
                sprintf(
                    'Could not find method named "%s" in class "%s" for factory of class "%s".',
                    $factory->getMethod(),
                    $factoryReflection->getName(),
                    $id,
                ),
            );
        }

        $reflectionMethod = $factoryReflection->getMethod($factory->getMethod());
        if (!$methodReturnType = $reflectionMethod->getReturnType()) {
            throw new ClassFactoryException(
                sprintf(
                    'Factory method "%s::%s" for class "%s" must have a return type.',
                    $factoryReflection->getName(),
                    $factory->getMethod(),
                    $id,
                ),
            );
        }

        if (!$methodReturnType instanceof ReflectionNamedType) {
            throw new ClassFactoryException(
                sprintf(
                    'Factory method "%s::%s" for class "%s" must have an explicit non-union and non-intersection type, got "%s".',
                    $factoryReflection->getName(),
                    $factory->getMethod(),
                    $id,
                    $methodReturnType,
                ),
            );
        }

        if (!$reflectionMethod->isStatic() && !$factoryReflection->isInstantiable()) {
            throw new ClassFactoryException(
                sprintf(
                    'Factory method "%s::%s" for class "%s" must be instantiable.',
                    $factoryReflection->getName(),
                    $factory->getMethod(),
                    $id,
                ),
            );
        }
    }

    /**
     * @throws ReflectionException
     */
    private function validateRootClass(ReflectionClass $factoryReflection, Factory $factory, string $id): void
    {
        $rootClassReflection = new ReflectionClass($id);

        $constructor = $rootClassReflection->getConstructor();
        $emptyConstructor = $rootClassReflection->hasMethod('__construct')
            ? $rootClassReflection->getMethod('__construct')
            : null;

        if (
            $factory->getId() !== $id
            && (
                ($constructor && !$constructor->isPublic())
                || ($emptyConstructor && !$emptyConstructor->isPublic())
            )
        ) {
            throw new ClassFactoryException(
                sprintf(
                    'Invalid factory method "%s::%s" for class "%s", as class "%s" has inaccessible constructor.',
                    $factoryReflection->getName(),
                    $factory->getMethod(),
                    $id,
                    $id,
                ),
            );
        }

        $returnType = $factoryReflection->getMethod($factory->getMethod())->getReturnType()->getName();
        if ($returnType !== 'self' && $returnType !== $id && !$rootClassReflection->isSubclassOf($returnType)) {
            throw new ClassFactoryException(
                sprintf(
                    'Factory method "%s::%s" for class "%s" must return compatible instance, got "%s".',
                    $factoryReflection->getName(),
                    $factory->getMethod(),
                    $id,
                    $returnType,
                ),
            );
        }
    }
}

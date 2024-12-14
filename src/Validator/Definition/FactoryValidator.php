<?php

declare(strict_types=1);

namespace Temkaa\Container\Validator\Definition;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use Temkaa\Container\Exception\ClassFactoryException;
use Temkaa\Container\Exception\Config\EntryNotFoundException;
use Temkaa\Container\Model\Config\Factory;
use function class_exists;
use function sprintf;

/**
 * @internal
 */
final readonly class FactoryValidator
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
        $this->validateFactoryMethod($factoryReflection, $factory, $id);
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
                    $factoryReflection->getName(),
                    $factory->getMethod(),
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
        $methodReturnType = $reflectionMethod->getReturnType();
        if (!$methodReturnType instanceof ReflectionNamedType) {
            /** @psalm-suppress PossiblyNullArgument */
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param ReflectionClass $factoryReflection
     * @param Factory         $factory
     * @param class-string    $id
     *
     * @return void
     *
     * @throws ReflectionException
     */
    private function validateFactoryMethod(ReflectionClass $factoryReflection, Factory $factory, string $id): void
    {
        $rootClassReflection = new ReflectionClass($id);

        $constructor = $rootClassReflection->getConstructor();
        if ($constructor && !$constructor->isPublic() && $factory->getId() !== $id) {
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

        /**
         * @psalm-suppress PossiblyNullReference, UndefinedMethod
         *
         * @var class-string|'self' $returnType
         */
        $returnType = $factoryReflection->getMethod($factory->getMethod())->getReturnType()?->getName();
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

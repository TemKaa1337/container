<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition\Configurator\Argument;

use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\SimpleContainer\Attribute\Bind\InstanceOfIterator;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Config\Factory;
use Temkaa\SimpleContainer\Model\Reference\Deferred\InstanceOfIteratorReference;
use Temkaa\SimpleContainer\Util\BoundVariableProvider;
use Temkaa\SimpleContainer\Util\Extractor\AttributeExtractor;

/**
 * @internal
 */
final readonly class InstanceOfIteratorConfigurator
{
    /**
     * @param Config              $config
     * @param ReflectionParameter $argument
     * @param class-string        $id
     * @param Factory|null        $factory
     *
     * @return InstanceOfIteratorReference|null
     */
    public function configure(
        Config $config,
        ReflectionParameter $argument,
        string $id,
        ?Factory $factory,
    ): ?InstanceOfIteratorReference {
        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();
        $argumentName = $argument->getName();

        $attribute = $argument->getAttributes(InstanceOfIterator::class);

        $configExpression = BoundVariableProvider::provide($config, $argumentName, $id, $factory);
        $argumentExpression = $attribute ? AttributeExtractor::extract($attribute, index: 0) : null;

        if ($configExpression === null && $argumentExpression === null) {
            return null;
        }

        if ($configExpression !== null && !$configExpression instanceof InstanceOfIterator) {
            return null;
        }

        $expression = $configExpression ?? $argumentExpression;

        $this->validateArgumentType($argumentType, $argumentName, $id);
        $this->validateClassExistence($argumentType, $argumentName, $id, $expression->id);

        return new InstanceOfIteratorReference($expression->id);
    }

    private function validateArgumentType(ReflectionNamedType $argumentType, string $argumentName, string $id): void
    {
        if (!in_array($argumentType->getName(), ['iterable', 'array'])) {
            throw new UnresolvableArgumentException(
                sprintf(
                    'Cannot instantiate entry "%s" with instance of iterator argument "%s::%s" as it\'s type is neither "array" or "iterable".',
                    $id,
                    $argumentName,
                    $argumentType->getName(),
                ),
            );
        }
    }

    private function validateClassExistence(
        ReflectionNamedType $argumentType,
        string $argumentName,
        string $id,
        string $instanceOfId,
    ): void {
        if (!class_exists($instanceOfId) && !interface_exists($instanceOfId)) {
            throw new UnresolvableArgumentException(
                sprintf(
                    'Cannot instantiate entry "%s" with instance of iterator "%s" for argument argument "%s::%s" as this class/interface does not exist.',
                    $id,
                    $instanceOfId,
                    $argumentName,
                    $argumentType->getName(),
                ),
            );
        }
    }
}

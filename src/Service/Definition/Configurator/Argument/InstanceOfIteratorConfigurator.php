<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Definition\Configurator\Argument;

use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
use Temkaa\Container\Exception\UnresolvableArgumentException;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Config\Factory;
use Temkaa\Container\Model\Reference\Deferred\InstanceOfIteratorReference;
use Temkaa\Container\Provider\BoundVariableProvider;
use Temkaa\Container\Service\Extractor\AttributeExtractor;
use function class_exists;
use function in_array;
use function interface_exists;
use function sprintf;

/**
 * @internal
 */
final readonly class InstanceOfIteratorConfigurator
{
    public function __construct(
        private AttributeExtractor $attributeExtractor,
        private BoundVariableProvider $boundVariableProvider = new BoundVariableProvider(),
    ) {
    }

    /**
     * @param class-string $id
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

        $configValue = $this->boundVariableProvider->provide($config, $argumentName, $id, $factory);
        $argumentValue = $attribute ? $this->attributeExtractor->extract($attribute, index: 0) : null;

        if (!$configValue->resolved && !$attribute) {
            return null;
        }

        if ($configValue->resolved && !$configValue->value instanceof InstanceOfIterator) {
            return null;
        }

        /** @var InstanceOfIterator $expression */
        $expression = $configValue->value ?? $argumentValue;

        $this->validateArgumentType($argumentType, $argumentName, $id);
        $this->validateClassExistence($argumentType, $argumentName, $id, $expression->id);

        return new InstanceOfIteratorReference(
            $expression->id,
            $expression->exclude,
            $expression->format,
            $expression->customFormatMapping,
        );
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

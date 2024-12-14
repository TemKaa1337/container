<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Definition\Configurator\Argument;

use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\Container\Attribute\Bind\TaggedIterator;
use Temkaa\Container\Exception\UnresolvableArgumentException;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Config\Factory;
use Temkaa\Container\Model\Reference\Deferred\TaggedIteratorReference;
use Temkaa\Container\Provider\BoundVariableProvider;
use Temkaa\Container\Service\Extractor\AttributeExtractor;
use function in_array;
use function sprintf;

/**
 * @internal
 */
final readonly class TaggedIteratorConfigurator
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
    ): ?TaggedIteratorReference {
        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();
        $argumentName = $argument->getName();

        $attribute = $argument->getAttributes(TaggedIterator::class);

        $configValue = $this->boundVariableProvider->provide($config, $argumentName, $id, $factory);
        $argumentValue = $attribute ? $this->attributeExtractor->extract($attribute, index: 0) : null;

        if (!$configValue->resolved && !$attribute) {
            return null;
        }

        if ($configValue->resolved && !$configValue->value instanceof TaggedIterator) {
            return null;
        }

        /** @var TaggedIterator $expression */
        $expression = $configValue->value ?? $argumentValue;
        $this->validateArgumentType($argumentType, $argumentName, $id);

        return new TaggedIteratorReference(
            $expression->tag,
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
                    'Cannot instantiate entry "%s" with tagged argument "%s::%s" as it\'s type is neither "array" or "iterable".',
                    $id,
                    $argumentName,
                    $argumentType->getName(),
                ),
            );
        }
    }
}

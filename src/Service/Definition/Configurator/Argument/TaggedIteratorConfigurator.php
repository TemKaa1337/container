<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition\Configurator\Argument;

use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\SimpleContainer\Attribute\Bind\TaggedIterator;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Config\Factory;
use Temkaa\SimpleContainer\Model\Reference\Deferred\TaggedIteratorReference;
use Temkaa\SimpleContainer\Util\BoundVariableProvider;
use Temkaa\SimpleContainer\Util\Extractor\AttributeExtractor;

/**
 * @internal
 */
final readonly class TaggedIteratorConfigurator
{
    /**
     * @param Config              $config
     * @param ReflectionParameter $argument
     * @param class-string        $id
     * @param Factory|null        $factory
     *
     * @return TaggedIteratorReference|null
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

        // TODO: extract same methods
        $configExpression = BoundVariableProvider::provide($config, $argumentName, $id, $factory);
        $argumentExpression = $attribute ? AttributeExtractor::extract($attribute, index: 0) : null;

        if ($configExpression === null && $argumentExpression === null) {
            return null;
        }

        if ($configExpression !== null && !$configExpression instanceof TaggedIterator) {
            return null;
        }

        $expression = $configExpression ?? $argumentExpression;
        $this->validateArgumentType($argumentType, $argumentName, $id);

        return new TaggedIteratorReference($expression->tag);
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

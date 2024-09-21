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
use Temkaa\Container\Util\BoundVariableProvider;
use Temkaa\Container\Util\Extractor\AttributeExtractor;

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

        $configExpression = BoundVariableProvider::provide($config, $argumentName, $id, $factory);
        $argumentExpression = $attribute ? AttributeExtractor::extract($attribute, index: 0) : null;

        if ($configExpression === null && $argumentExpression === null) {
            return null;
        }

        if ($configExpression !== null && !$configExpression instanceof TaggedIterator) {
            return null;
        }

        /** @var TaggedIterator $expression */
        $expression = $configExpression ?? $argumentExpression;
        $this->validateArgumentType($argumentType, $argumentName, $id);

        return new TaggedIteratorReference($expression->tag, $expression->exclude);
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

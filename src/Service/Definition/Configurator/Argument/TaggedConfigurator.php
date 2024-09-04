<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition\Configurator\Argument;

use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\SimpleContainer\Attribute\Bind\Tagged;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Config\Factory;
use Temkaa\SimpleContainer\Model\Reference\Deferred\TaggedReference;
use Temkaa\SimpleContainer\Util\BoundVariableProvider;
use Temkaa\SimpleContainer\Util\Extractor\AttributeExtractor;

/**
 * @internal
 */
final readonly class TaggedConfigurator
{
    /**
     * @param Config              $config
     * @param ReflectionParameter $argument
     * @param class-string        $id
     * @param Factory|null        $factory
     *
     * @return TaggedReference|null
     */
    public function configure(
        Config $config,
        ReflectionParameter $argument,
        string $id,
        ?Factory $factory,
    ): ?TaggedReference {
        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();
        $argumentName = $argument->getName();

        /** @var string[] $argumentAttributes */
        $argumentAttributes = AttributeExtractor::extractParameters(
            $argument->getAttributes(Tagged::class),
            parameter: 'tag',
        );

        $configExpression = BoundVariableProvider::provide($config, $argumentName, $id, $factory);
        $argumentExpression = $argumentAttributes ? current($argumentAttributes) : null;

        if ($configExpression === null && $argumentExpression === null) {
            return null;
        }

        if ($configExpression === null) {
            $this->validateArgumentType($argumentType, $argumentName, $id);

            return new TaggedReference($argumentExpression);
        }

        if (!$configExpression instanceof Tagged) {
            return null;
        }

        $this->validateArgumentType($argumentType, $argumentName, $id);

        /** @psalm-suppress PossiblyInvalidArgument */
        return new TaggedReference($configExpression->tag);
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

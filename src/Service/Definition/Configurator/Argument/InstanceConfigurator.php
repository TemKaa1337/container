<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Definition\Configurator\Argument;

use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\Container\Attribute\Bind\Instance;
use Temkaa\Container\Exception\UnresolvableArgumentException;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Config\Factory;
use Temkaa\Container\Model\Reference\Reference;
use Temkaa\Container\Provider\BoundVariableProvider;
use Temkaa\Container\Service\Extractor\AttributeExtractor;
use function class_implements;
use function in_array;
use function is_subclass_of;
use function sprintf;

/**
 * @internal
 */
final readonly class InstanceConfigurator
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
    ): ?Reference {
        $attribute = $argument->getAttributes(Instance::class);

        $configValue = $this->boundVariableProvider->provide($config, $argument->getName(), $id, $factory);
        $argumentValue = $attribute ? $this->attributeExtractor->extract($attribute, index: 0) : null;

        if (!$configValue->resolved && !$attribute) {
            return null;
        }

        if ($configValue->resolved && !$configValue->value instanceof Instance) {
            return null;
        }

        /** @var Instance $instance */
        $instance = $configValue->value ?? $argumentValue;

        $this->validate($instance->id, $argument, $id);

        return new Reference($instance->id);
    }

    private function validate(string $instanceId, ReflectionParameter $argument, string $id): void
    {
        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();
        $argumentName = $argument->getName();
        /** @var class-string|'object' $argumentTypeName */
        $argumentTypeName = $argumentType->getName();

        if (
            $argumentTypeName !== 'object'
            && $argumentTypeName !== $instanceId
            && !is_subclass_of($instanceId, $argumentTypeName)
            && !in_array($argumentTypeName, class_implements($instanceId), true)
        ) {
            throw new UnresolvableArgumentException(
                sprintf(
                    'Cannot instantiate entry "%s" with instance argument "%s::%s" as it\'s type '
                    .'is not subtype of bounded instance: "%s".',
                    $id,
                    $argumentName,
                    $argumentTypeName,
                    $instanceId,
                ),
            );
        }
    }
}

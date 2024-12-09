<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Definition\Configurator\Argument;

use Psr\Container\ContainerExceptionInterface;
use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\Container\Attribute\Bind\Parameter;
use Temkaa\Container\Exception\Config\EnvVariableNotFoundException;
use Temkaa\Container\Exception\UnresolvableArgumentException;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Config\Factory;
use Temkaa\Container\Model\Value;
use Temkaa\Container\Service\Type\Resolver;
use Temkaa\Container\Util\BoundVariableProvider;
use Temkaa\Container\Util\ExpressionParser;
use Temkaa\Container\Util\Extractor\AttributeExtractor;
use function current;
use function is_string;
use function sprintf;

/**
 * @internal
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final readonly class BoundVariableConfigurator
{
    private ExpressionParser $expressionParser;

    private Resolver $typeResolver;

    public function __construct()
    {
        $this->expressionParser = new ExpressionParser();
        $this->typeResolver = new Resolver();
    }

    /**
     * @param class-string $id
     *
     * @throws ContainerExceptionInterface
     */
    public function configure(
        Config $config,
        ReflectionParameter $argument,
        string $id,
        ?Factory $factory,
    ): Value {
        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();
        $argumentName = $argument->getName();
        $argumentTypeName = $argumentType->getName();

        $value = $this->getExpression($config, $argument, $id, $factory);
        if (!$value->resolved) {
            if (!$argumentType->isBuiltin()) {
                return new Value(null, resolved: false);
            }

            if ($argument->isDefaultValueAvailable()) {
                return new Value($argument->getDefaultValue(), resolved: true);
            }

            if ($argumentType->allowsNull()) {
                return new Value(null, resolved: true);
            }

            throw new UnresolvableArgumentException(
                sprintf(
                    'Cannot instantiate entry "%s" with argument "%s::%s".',
                    $id,
                    $argumentName,
                    $argumentTypeName,
                ),
            );
        }

        try {
            /** @psalm-suppress MixedAssignment */
            $expression = is_string($value->value) ? $this->expressionParser->parse($value->value) : $value->value;
        } catch (EnvVariableNotFoundException $exception) {
            if (!$argument->isDefaultValueAvailable()) {
                throw $exception;
            }

            return new Value($argument->getDefaultValue(), resolved: true);
        }

        return new Value($this->typeResolver->resolve($expression, $argument, $id), resolved: true);
    }

    /**
     * @param class-string $id
     */
    private function getExpression(
        Config $config,
        ReflectionParameter $argument,
        string $id,
        ?Factory $factory,
    ): Value {
        $argumentName = $argument->getName();

        $argumentAttributes = AttributeExtractor::extractParameters(
            $argument->getAttributes(Parameter::class),
            parameter: 'expression',
        );

        $configExpression = BoundVariableProvider::provide($config, $argumentName, $id, $factory);
        if ($configExpression->resolved) {
            return $configExpression;
        }

        if ($argumentAttributes) {
            return new Value(current($argumentAttributes), resolved: true);
        }

        return new Value(null, resolved: false);
    }
}

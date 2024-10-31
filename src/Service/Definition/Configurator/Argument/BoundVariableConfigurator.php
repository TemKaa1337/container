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
use Temkaa\Container\Util\BoundVariableProvider;
use Temkaa\Container\Util\ExpressionParser;
use Temkaa\Container\Util\Extractor\AttributeExtractor;
use Temkaa\Container\Util\TypeCaster;
use Temkaa\Container\Validator\Definition\Argument\ExpressionTypeCompatibilityValidator;
use UnitEnum;

/**
 * @internal
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final readonly class BoundVariableConfigurator
{
    private ExpressionParser $expressionParser;

    public function __construct()
    {
        $this->expressionParser = new ExpressionParser();
    }

    /**
     * @param Config              $config
     * @param ReflectionParameter $argument
     * @param class-string        $id
     * @param Factory|null        $factory
     *
     * @return array{value: mixed, resolved: boolean}
     *
     * @throws ContainerExceptionInterface
     */
    public function configure(
        Config $config,
        ReflectionParameter $argument,
        string $id,
        ?Factory $factory,
    ): array {
        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();
        $argumentName = $argument->getName();
        $argumentTypeName = $argumentType->getName();

        $expression = $this->getExpression($config, $argument, $id, $factory);
        if ($expression === null) {
            if (!$argumentType->isBuiltin()) {
                return ['value' => null, 'resolved' => false];
            }

            if ($argument->isDefaultValueAvailable()) {
                return ['value' => $argument->getDefaultValue(), 'resolved' => true];
            }

            if ($argumentType->allowsNull()) {
                return ['value' => null, 'resolved' => true];
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

        (new ExpressionTypeCompatibilityValidator())->validate($expression, $argument, $id);

        try {
            /** @psalm-suppress MixedAssignment */
            $value = $expression instanceof UnitEnum
                ? $expression
                : TypeCaster::cast($this->expressionParser->parse($expression), $argumentTypeName);

            return ['value' => $value, 'resolved' => true];
        } catch (EnvVariableNotFoundException $exception) {
            if (!$argument->isDefaultValueAvailable()) {
                throw $exception;
            }

            return ['value' => $argument->getDefaultValue(), 'resolved' => true];
        }
    }

    /**
     * @param Config              $config
     * @param ReflectionParameter $argument
     * @param class-string        $id
     * @param Factory|null        $factory
     *
     * @return string|UnitEnum|null
     */
    private function getExpression(
        Config $config,
        ReflectionParameter $argument,
        string $id,
        ?Factory $factory,
    ): string|null|UnitEnum {
        $argumentName = $argument->getName();

        $argumentAttributes = AttributeExtractor::extractParameters(
            $argument->getAttributes(Parameter::class),
            parameter: 'expression',
        );

        /** @var string|UnitEnum|null $configExpression */
        $configExpression = BoundVariableProvider::provide($config, $argumentName, $id, $factory);
        $argumentExpression = $argumentAttributes ? current($argumentAttributes) : null;

        return $configExpression ?? $argumentExpression ?? null;
    }
}

<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition\Configurator\Argument;

use Psr\Container\ContainerExceptionInterface;
use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\SimpleContainer\Attribute\Bind\Parameter;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Config\Factory;
use Temkaa\SimpleContainer\Util\BoundVariableProvider;
use Temkaa\SimpleContainer\Util\ExpressionParser;
use Temkaa\SimpleContainer\Util\Extractor\AttributeExtractor;
use Temkaa\SimpleContainer\Util\TypeCaster;
use Temkaa\SimpleContainer\Validator\Definition\Argument\ExpressionTypeCompatibilityValidator;
use UnitEnum;

/**
 * @internal
 */
final readonly class OtherConfigurator
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
        $argumentTypeName = $argumentType->getName();
        $argumentName = $argument->getName();

        $argumentAttributes = AttributeExtractor::extractParameters(
            $argument->getAttributes(Parameter::class),
            parameter: 'expression',
        );

        $configExpression = BoundVariableProvider::provide($config, $argumentName, $id, $factory);
        $argumentExpression = $argumentAttributes ? current($argumentAttributes) : null;

        if ($configExpression === null && $argumentExpression === null) {
            if (!$argumentType->isBuiltin()) {
                return ['value' => null, 'resolved' => false];
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

        $expression = $configExpression ?? $argumentExpression;

        (new ExpressionTypeCompatibilityValidator())->validate($expression, $argument, $id);

        return [
            'value'    => $expression instanceof UnitEnum
                ? $expression
                : TypeCaster::cast($this->expressionParser->parse($expression), $argumentTypeName),
            'resolved' => true,
        ];
    }
}

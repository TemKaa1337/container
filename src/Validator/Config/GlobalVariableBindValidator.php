<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Temkaa\SimpleContainer\Enum\Config\Structure;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;
use Temkaa\SimpleContainer\Util\ExpressionParser;

/**
 * @internal
 */
final class GlobalVariableBindValidator implements ValidatorInterface
{
    public function validate(array $config): void
    {
        if (!$bindNode = $config[Structure::Services->value][Structure::Bind->value] ?? []) {
            return;
        }

        if (!is_array($bindNode) || array_is_list($bindNode)) {
            throw new InvalidConfigNodeTypeException(
                'Node "services.bind" must be of "array<string, string>" type.',
            );
        }

        $expressionParser = new ExpressionParser();
        foreach ($bindNode as $variableName => $variableValue) {
            if (!is_string($variableName) || !is_string($variableValue)) {
                throw new InvalidConfigNodeTypeException(
                    'Node "services.bind" must be of "array<string, string>" type.',
                );
            }

            // expression parser itself throws exception if anything went wrong
            $expressionParser->parse($variableValue);
        }
    }
}

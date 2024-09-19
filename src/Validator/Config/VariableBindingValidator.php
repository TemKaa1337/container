<?php

declare(strict_types=1);

namespace Temkaa\Container\Validator\Config;

use Temkaa\Container\Model\Config;
use Temkaa\Container\Util\ExpressionParser;

/**
 * @internal
 */
final class VariableBindingValidator implements ValidatorInterface
{
    public function validate(Config $config): void
    {
        $expressionParser = new ExpressionParser();
        foreach ($config->getBoundedVariables() as $variableValue) {
            if (is_string($variableValue)) {
                $expressionParser->parse($variableValue);
            }
        }

        $boundedClasses = $config->getBoundedClasses();
        foreach ($boundedClasses as $class) {
            foreach ($class->getBoundedVariables() as $variableValue) {
                if (is_string($variableValue)) {
                    $expressionParser->parse($variableValue);
                }
            }
        }
    }
}

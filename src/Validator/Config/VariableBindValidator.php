<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Util\ExpressionParser;

/**
 * @internal
 */
final class VariableBindValidator implements ValidatorInterface
{
    public function validate(Config $config): void
    {
        $expressionParser = new ExpressionParser();
        foreach ($config->getBoundedVariables() as $variableValue) {
            $expressionParser->parse($variableValue);
        }

        $boundedClasses = $config->getBoundedClasses();
        foreach ($boundedClasses as $class) {
            foreach ($class->getBoundedVariables() as $variableValue) {
                $expressionParser->parse($variableValue);
            }
        }
    }
}

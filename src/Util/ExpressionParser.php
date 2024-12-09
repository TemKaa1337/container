<?php

declare(strict_types=1);

namespace Temkaa\Container\Util;

use Temkaa\Container\Exception\Config\EnvVariableCircularException;
use Temkaa\Container\Exception\Config\EnvVariableNotFoundException;
use function preg_match_all;
use function sprintf;
use function str_replace;

/**
 * @internal
 */
final class ExpressionParser
{
    private const string ENV_VARIABLE_PATTERN = '#env\((.*?)\)#';

    public function parse(string $expression): string
    {
        foreach ($this->getExpressions($expression) as $binding) {
            if (!Env::has($binding)) {
                throw new EnvVariableNotFoundException(
                    sprintf('Variable "%s" is not found in env variables.', $binding),
                );
            }

            $expression = str_replace(
                sprintf('env(%s)', $binding),
                Env::get($binding),
                $expression,
            );

            if (Flag::isToggled($binding, group: 'env')) {
                throw new EnvVariableCircularException($expression, Flag::getToggled(group: 'env'));
            }

            Flag::toggle($binding, group: 'env');

            if ($this->getExpressions($expression)) {
                $expression = self::parse($expression);
            }

            Flag::untoggle($binding, group: 'env');
        }

        return $expression;
    }

    /**
     * @return string[]
     */
    private function getExpressions(string $variable): array
    {
        $matches = [];
        preg_match_all(self::ENV_VARIABLE_PATTERN, $variable, matches: $matches);

        return $matches[1] ?? [];
    }
}

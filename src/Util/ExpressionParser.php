<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Util;

final class ExpressionParser
{
    public static function parse(string $expression): string
    {
        $matches = [];
        preg_match_all('#env\((.*?)\)#', $expression, matches: $matches);

        $envVarsBindings = $matches[1] ?? [];
        if ($envVarsBindings) {
            foreach ($envVarsBindings as $binding) {
                $expression = str_replace(
                    sprintf('env(%s)', $binding),
                    Env::get($binding),
                    $expression,
                );
            }
        }

        return $expression;
    }
}

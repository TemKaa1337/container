<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer;

final readonly class ExpressionParser
{
    public function __construct(private array $env)
    {
    }

    public function parse(string $expression): string
    {
        $matches = [];
        preg_match_all('#env\((.*?)\)#', $expression, matches: $matches);

        $envVarsBindings = $matches[1] ?? [];
        if ($envVarsBindings) {
            foreach ($envVarsBindings as $binding) {
                $expression = str_replace(
                    sprintf('env(%s)', $binding),
                    $this->env[$binding],
                    $expression,
                );
            }
        }

        return $expression;
    }
}

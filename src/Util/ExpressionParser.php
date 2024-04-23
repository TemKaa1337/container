<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Util;

use Temkaa\SimpleContainer\Exception\Config\EnvVariableCircularException;

final class ExpressionParser
{
    private const ENV_VARIABLE_PATTERN = '#env\((.*?)\)#';

    private array $variablesResolving = [];

    public function parse(string $expression): string
    {
        if (!$envVarsBindings = $this->getExpressions($expression)) {
            return $expression;
        }

        foreach ($envVarsBindings as $binding) {
            $expression = str_replace(
                sprintf('env(%s)', $binding),
                Env::get($binding),
                $expression,
            );

            if ($this->isResolving($binding)) {
                throw new EnvVariableCircularException($expression, array_keys($this->variablesResolving));
            }

            $this->setResolving($binding, isResolving: true);

            if ($this->getExpressions($expression)) {
                $expression = self::parse($expression);
            }

            $this->setResolving($binding, isResolving: false);
        }

        return $expression;
    }

    private function getExpressions(string $variable): array
    {
        $matches = [];
        preg_match_all(self::ENV_VARIABLE_PATTERN, $variable, matches: $matches);

        return $matches[1] ?? [];
    }

    private function setResolving(string $variableName, bool $isResolving): void
    {
        if ($isResolving) {
            $this->variablesResolving[$variableName] = true;
        } else {
            unset($this->variablesResolving[$variableName]);
        }
    }

    private function isResolving(string $variableName): bool
    {
        return $this->variablesResolving[$variableName] ?? false;
    }
}

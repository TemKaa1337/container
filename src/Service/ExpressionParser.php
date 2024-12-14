<?php

declare(strict_types=1);

namespace Temkaa\Container\Service;

use Temkaa\Container\Exception\Config\EnvVariableCircularException;
use Temkaa\Container\Exception\Config\EnvVariableNotFoundException;
use Temkaa\Container\Util\Env;
use Temkaa\Container\Util\FlagManager;
use function preg_match_all;
use function sprintf;
use function str_replace;

/**
 * @internal
 */
final readonly class ExpressionParser
{
    private const string ENV_VARIABLE_PATTERN = '#env\((.*?)\)#';

    public function __construct(
        private FlagManager $flagManager = new FlagManager(),
    ) {
    }

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

            if ($this->flagManager->isToggled($binding)) {
                throw new EnvVariableCircularException($expression, $this->flagManager->getToggled());
            }

            $this->flagManager->toggle($binding);

            if ($this->getExpressions($expression)) {
                $expression = $this->parse($expression);
            }

            $this->flagManager->untoggle($binding);
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

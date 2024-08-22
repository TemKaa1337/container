<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Exception\Config;

use LogicException;

final class EnvVariableCircularException extends LogicException
{
    /**
     * @param string   $variableName
     * @param string[] $references
     */
    public function __construct(string $variableName, array $references)
    {
        $message = sprintf(
            'Cannot resolve env variable "%s" as it has circular references "%s".',
            $variableName,
            implode(' -> ', $references),
        );

        parent::__construct($message);
    }
}

<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Psr\Container\ContainerExceptionInterface;
use Temkaa\SimpleContainer\Exception\InvalidEnvVariableValueException;

final class EnvValidator
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function validate(array $env): void
    {
        foreach ($env as $name => $value) {
            if (!is_string($name) || !is_string($value)) {
                throw new InvalidEnvVariableValueException(
                    'Parameter variables format must be of "array<string, string>" type.',
                );
            }
        }
    }
}

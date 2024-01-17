<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Psr\Container\ContainerExceptionInterface;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;

final class ConfigDirectoryNodeValidator
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function validate(array $config): void
    {
        if (!isset($config['config_dir'])) {
            throw new InvalidConfigNodeTypeException('Node "config_dir" must be of "string" type.');
        }

        if (!is_string($config['config_dir'])) {
            throw new InvalidConfigNodeTypeException('Node "config_dir" must be of "string" type.');
        }
    }
}

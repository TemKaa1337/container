<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Psr\Container\ContainerExceptionInterface;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;
use Temkaa\SimpleContainer\Exception\Config\InvalidPathException;

final class ServicesNodeValidator
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function validate(array $config, string $configDir): void
    {
        if (isset($config['services'])) {
            if (!is_array($config['services'])) {
                throw new InvalidConfigNodeTypeException(
                    'Node "services" must be of "array<include|exclude, array>" type.',
                );
            }

            if (isset($config['services']['include'])) {
                if (!is_array($config['services']['include'])) {
                    throw new InvalidConfigNodeTypeException(
                        'Node "services.include" must be of "array<int, array>" type.',
                    );
                }

                if (!array_is_list($config['services']['include'])) {
                    throw new InvalidConfigNodeTypeException(
                        'Node "services.include" must be of "array<int, array>" type.',
                    );
                }
            }

            if (isset($config['services']['exclude'])) {
                if (!is_array($config['services']['exclude'])) {
                    throw new InvalidConfigNodeTypeException(
                        'Node "services.exclude" must be of "array<int, array>" type.',
                    );
                }

                if (!array_is_list($config['services']['exclude'])) {
                    throw new InvalidConfigNodeTypeException(
                        'Node "services.include" must be of "array<int, array>" type.',
                    );
                }
            }

            foreach ($config['services']['include'] ?? [] as $classPath) {
                $dir = realpath($configDir.'/'.$classPath);
                if ($dir === false) {
                    throw new InvalidPathException(sprintf('The specified path "%s" does not exist.', $classPath));
                }
            }

            foreach ($config['services']['exclude'] ?? [] as $classPath) {
                $dir = realpath($configDir.'/'.$classPath);
                if ($dir === false) {
                    throw new InvalidPathException(sprintf('The specified path "%s" does not exist.', $classPath));
                }
            }
        }
    }
}

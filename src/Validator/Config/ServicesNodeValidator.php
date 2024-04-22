<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Psr\Container\ContainerExceptionInterface;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;
use Temkaa\SimpleContainer\Exception\Config\InvalidPathException;

final class ServicesNodeValidator implements ValidatorInterface
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function validate(array $config): void
    {
        if (!isset($config['services'])) {
            return;
        }

        if (!is_array($config['services'])) {
            throw new InvalidConfigNodeTypeException(
                'Node "services" must be of "array<include|exclude, array>" type.',
            );
        }

        $this->validateNode($config, name: 'include');
        $this->validateNode($config, name: 'exclude');
    }

    private function validateNode(array $config, string $name): void
    {
        $configDir = $config['file']->getPath();

        if (!isset($config['services'][$name])) {
            return;
        }

        if (!is_array($config['services'][$name])) {
            throw new InvalidConfigNodeTypeException(
                sprintf('Node "services.%s" must be of "array<int, array>" type.', $name),
            );
        }

        if (!array_is_list($config['services'][$name])) {
            throw new InvalidConfigNodeTypeException(
                sprintf('Node "services.%s" must be of "array<int, array>" type.', $name),
            );
        }

        foreach ($config['services'][$name] as $classPath) {
            if (realpath($configDir.'/'.$classPath) === false) {
                throw new InvalidPathException(sprintf('The specified path "%s" does not exist.', $classPath));
            }
        }
    }
}

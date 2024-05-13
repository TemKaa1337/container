<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Psr\Container\ContainerExceptionInterface;
use SplFileInfo;
use Temkaa\SimpleContainer\Enum\Config\Structure;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;
use Temkaa\SimpleContainer\Exception\Config\InvalidPathException;

/**
 * @internal
 */
final class ServicesNodeValidator implements ValidatorInterface
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function validate(array $config): void
    {
        $services = $config[Structure::Services->value] ?? [];
        if ($services === []) {
            return;
        }

        if (!is_array($services) || array_is_list($services)) {
            throw new InvalidConfigNodeTypeException(
                'Node "services" must be of "array<string, array|string>" type.',
            );
        }

        $this->validateNode($services, $config[Structure::File->value], name: Structure::Include);
        $this->validateNode($services, $config[Structure::File->value], name: Structure::Exclude);
    }

    private function validateNode(array $services, SplFileInfo $file, Structure $name): void
    {
        $configDir = $file->getPath();

        if (!isset($services[$name->value])) {
            return;
        }

        if (!is_array($services[$name->value])) {
            throw new InvalidConfigNodeTypeException(
                sprintf('Node "services.%s" must be of "array<int, array>" type.', $name->value),
            );
        }

        if (!array_is_list($services[$name->value])) {
            throw new InvalidConfigNodeTypeException(
                sprintf('Node "services.%s" must be of "array<int, array>" type.', $name->value),
            );
        }

        foreach ($services[$name->value] as $classPath) {
            if (realpath($configDir.'/'.$classPath) === false) {
                throw new InvalidPathException(sprintf('The specified path "%s" does not exist.', $classPath));
            }
        }
    }
}

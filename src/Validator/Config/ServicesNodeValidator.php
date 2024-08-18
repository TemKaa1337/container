<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Temkaa\SimpleContainer\Exception\Config\InvalidPathException;
use Temkaa\SimpleContainer\Model\Container\ConfigNew;

/**
 * @internal
 */
final class ServicesNodeValidator implements ValidatorInterface
{
    public function validate(ConfigNew $config): void
    {
        $this->validateNode($config->getIncludedPaths());
        $this->validateNode($config->getExcludedPaths());
    }

    /**
     * @param string[] $paths
     */
    private function validateNode(array $paths): void
    {
        foreach ($paths as $path) {
            if (!realpath($path)) {
                throw new InvalidPathException(sprintf('The specified path "%s" does not exist.', $path));
            }
        }
    }
}

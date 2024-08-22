<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Temkaa\SimpleContainer\Exception\Config\InvalidPathException;
use Temkaa\SimpleContainer\Model\Config;

/**
 * @internal
 */
final class PathValidator implements ValidatorInterface
{
    public function validate(Config $config): void
    {
        $this->validatePaths($config->getIncludedPaths());
        $this->validatePaths($config->getExcludedPaths());
    }

    /**
     * @param string[] $paths
     */
    private function validatePaths(array $paths): void
    {
        foreach ($paths as $path) {
            if (!realpath($path)) {
                throw new InvalidPathException(sprintf('The specified path "%s" does not exist.', $path));
            }
        }
    }
}

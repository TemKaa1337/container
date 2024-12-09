<?php

declare(strict_types=1);

namespace Temkaa\Container\Validator\Config;

use Temkaa\Container\Exception\Config\InvalidPathException;
use Temkaa\Container\Model\Config;
use function realpath;
use function sprintf;

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
            /** @psalm-suppress RiskyTruthyFalsyComparison */
            if (!realpath($path)) {
                throw new InvalidPathException(sprintf('The specified path "%s" does not exist.', $path));
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Temkaa\Container\Attribute\Bind;

use InvalidArgumentException;
use function is_string;

abstract readonly class AbstractIterator
{
    /**
     * @param array<class-string, string> $mapping
     */
    protected function validate(array $mapping): void
    {
        foreach ($mapping as $className => $signature) {
            /** @psalm-suppress DocblockTypeContradiction */
            if (!is_string($className) || !is_string($signature)) {
                throw new InvalidArgumentException('Class name and signature in custom mapping must be strings.');
            }
        }
    }
}

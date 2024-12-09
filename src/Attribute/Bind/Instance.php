<?php

declare(strict_types=1);

namespace Temkaa\Container\Attribute\Bind;

use Attribute;
use InvalidArgumentException;
use function class_exists;
use function sprintf;

/**
 * @api
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Instance
{
    /**
     * @param class-string $id
     */
    public function __construct(
        public string $id,
    ) {
        if (!class_exists($this->id)) {
            throw new InvalidArgumentException(sprintf('Cannot find class with id: "%s".', $this->id));
        }
    }
}

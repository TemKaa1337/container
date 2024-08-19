<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Reference\Deferred;

use Temkaa\SimpleContainer\Model\Reference\ReferenceInterface;

/**
 * @internal
 */
final readonly class DecoratorReference implements ReferenceInterface
{
    /**
     * @param class-string $id
     */
    public function __construct(
        public string $id,
        public int $priority,
        public string $signature,
    ) {
    }

    /**
     * @return class-string
     */
    public function getId(): string
    {
        return $this->id;
    }
}

<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Reference;

/**
 * @internal
 */
final readonly class Reference implements ReferenceInterface
{
    /**
     * @param class-string $id
     */
    public function __construct(
        public string $id,
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

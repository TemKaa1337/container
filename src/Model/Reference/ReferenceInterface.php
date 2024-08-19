<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Reference;

/**
 * @internal
 */
interface ReferenceInterface
{
    /**
     * @return class-string|string
     */
    public function getId(): string;
}

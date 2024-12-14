<?php

declare(strict_types=1);

namespace Temkaa\Container\Util;

use function array_keys;

/**
 * @internal
 */
final class FlagManager
{
    /**
     * @var array<string, true>
     */
    private array $flags = [];

    /**
     * @return list<string>
     */
    public function getToggled(): array
    {
        return array_keys($this->flags);
    }

    public function isToggled(string $name): bool
    {
        return isset($this->flags[$name]);
    }

    public function toggle(string $name): void
    {
        $this->flags[$name] = true;
    }

    public function untoggle(string $name): void
    {
        unset($this->flags[$name]);
    }
}

<?php

declare(strict_types=1);

namespace Temkaa\Container\Factory\Definition;

use Temkaa\Container\Model\Definition\DefinitionInterface;
use Temkaa\Container\Model\Definition\InterfaceDefinition;

/**
 * @internal
 */
final class InterfaceFactory
{
    /**
     * @param class-string $id
     * @param class-string $implementedById
     */
    public static function create(string $id, string $implementedById): DefinitionInterface
    {
        return (new InterfaceDefinition())
            ->setId($id)
            ->setImplementedById($implementedById);
    }
}

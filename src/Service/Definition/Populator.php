<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition;

use ReflectionClass;
use Temkaa\SimpleContainer\Attribute\Alias;
use Temkaa\SimpleContainer\Attribute\Decorates;
use Temkaa\SimpleContainer\Attribute\Tag;
use Temkaa\SimpleContainer\Factory\Definition\DecoratorFactory;
use Temkaa\SimpleContainer\Factory\Definition\InterfaceFactory;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Util\Extractor\AttributeExtractor;

final class Populator
{
    public function populate(
        ClassDefinition $definition,
        ReflectionClass $reflection,
        Config $config,
        Bag $definitions,
    ): void {
        $classTags = $reflection->getAttributes(Tag::class);
        $classAliases = $reflection->getAttributes(Alias::class);

        $boundClassInfo = $config->getBoundedClass($reflection->getName());

        /** @var string[] $aliases */
        $aliases = array_merge(
            AttributeExtractor::extractParameters($classAliases, parameter: 'name'),
            $config->getBoundedClass($definition->getId())?->getAliases() ?? [],
        );

        /** @var string[] $tags */
        $tags = AttributeExtractor::extractParameters($classTags, parameter: 'name');
        $definition
            ->addTags($boundClassInfo?->getTags() ?? [])
            ->addTags($tags)
            ->setAliases($aliases);

        $interfaces = $reflection->getInterfaces();
        $definition->setImplements(array_keys($interfaces));
        foreach ($interfaces as $interface) {
            $interfaceName = $interface->getName();

            if (
                $config->hasBoundInterface($interfaceName)
                && $config->getBoundInterfaceImplementation($interfaceName) === $reflection->getName()
            ) {
                $definitions->add(
                    InterfaceFactory::create(
                        $interfaceName,
                        implementedById: $reflection->getName(),
                    ),
                );
            }

            $interfaceTags = $interface->getAttributes(Tag::class);
            /** @var string[] $tags */
            $tags = AttributeExtractor::extractParameters($interfaceTags, parameter: 'name');
            $definition->addTags($tags);
        }

        if ($decorates = $boundClassInfo?->getDecorates()) {
            $definition->setDecorates($decorates);
        } else if ($decoratesAttribute = $reflection->getAttributes(Decorates::class)) {
            $decoratesAttribute = AttributeExtractor::extract($decoratesAttribute, index: 0);

            $definition->setDecorates(DecoratorFactory::createFromAttribute($decoratesAttribute));
        }
    }
}

<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Definition;

use ReflectionClass;
use Temkaa\Container\Attribute\Alias;
use Temkaa\Container\Attribute\Decorates;
use Temkaa\Container\Attribute\Tag;
use Temkaa\Container\Factory\Definition\DecoratorFactory;
use Temkaa\Container\Factory\Definition\InterfaceFactory;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Config\ClassConfig;
use Temkaa\Container\Model\Definition\Bag;
use Temkaa\Container\Model\Definition\ClassDefinition;
use Temkaa\Container\Util\Extractor\AttributeExtractor;
use function array_merge;
use function array_unique;

/**
 * @internal
 */
final class Populator
{
    public function populate(
        ClassDefinition $definition,
        ReflectionClass $reflection,
        Config $config,
        Bag $definitions,
    ): void {
        $classAliases = $reflection->getAttributes(Alias::class);

        $boundClassInfo = $config->getBoundedClass($reflection->getName());

        $parentClasses = $this->getParentClasses($reflection);
        $interfaces = $reflection->getInterfaceNames();

        $tags = array_merge(
            $this->getTags($config, $parentClasses),
            $this->getTags($config, $interfaces),
            $this->getTags($config, [$definition->getId()]),
        );

        /** @var string[] $aliases */
        $aliases = array_merge(
            AttributeExtractor::extractParameters($classAliases, parameter: 'name'),
            $config->getBoundedClass($definition->getId())?->getAliases() ?? [],
        );

        $definition
            ->setTags(array_unique($tags))
            ->setAliases($aliases)
            ->setInstanceOf($parentClasses)
            ->setImplements($interfaces);

        foreach ($interfaces as $interface) {
            if (
                $config->hasBoundInterface($interface)
                && $config->getBoundInterfaceImplementation($interface) === $reflection->getName()
            ) {
                $definitions->add(
                    InterfaceFactory::create(
                        $interface,
                        implementedById: $reflection->getName(),
                    ),
                );
            }
        }

        $this->addDecorator($reflection, $boundClassInfo, $definition);
    }

    private function addDecorator(
        ReflectionClass $reflection,
        ?ClassConfig $classConfig,
        ClassDefinition $definition,
    ): void {
        if ($decorates = $classConfig?->getDecorates()) {
            $definition->setDecorates($decorates);
        } else if ($decoratesAttribute = $reflection->getAttributes(Decorates::class)) {
            $decoratesAttribute = AttributeExtractor::extract($decoratesAttribute, index: 0);

            $definition->setDecorates(DecoratorFactory::createFromAttribute($decoratesAttribute));
        }
    }

    /**
     * @return class-string[]
     */
    private function getParentClasses(ReflectionClass $reflection): array
    {
        $parentClasses = [];

        while ($reflection = $reflection->getParentClass()) {
            $parentClasses[] = $reflection->getName();
        }

        return $parentClasses;
    }

    /**
     * @param Config         $config
     * @param class-string[] $ids
     *
     * @return list<string>
     */
    private function getTags(Config $config, array $ids): array
    {
        /** @var list<list<string>> $tags */
        $tags = [];
        foreach ($ids as $id) {
            $reflection = new ReflectionClass($id);

            /** @var list<string> $entryTags */
            $entryTags = AttributeExtractor::extractParameters(
                $reflection->getAttributes(Tag::class),
                parameter: 'name',
            );

            /** @var list<string> $configTags */
            $configTags = $config->getBoundedClass($id)?->getTags() ?? [];

            $tags[] = $entryTags;
            $tags[] = $configTags;
        }

        return array_merge(...$tags);
    }
}

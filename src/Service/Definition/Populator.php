<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition;

use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Attribute\Alias;
use Temkaa\SimpleContainer\Attribute\Decorates;
use Temkaa\SimpleContainer\Attribute\Tag;
use Temkaa\SimpleContainer\Factory\Definition\DecoratorFactory;
use Temkaa\SimpleContainer\Factory\Definition\InterfaceFactory;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Config\ClassConfig;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Util\Extractor\AttributeExtractor;

/**
 * @internal
 */
final class Populator
{
    /**
     * @throws ReflectionException
     */
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

        // TODO: add tests on tag inheritance from abstract classes
        // TODO: write tests on abstract classes which extend each other, will they be also written here
        // TODO: update doc and examples with instanceofiterator
        $instanceOf = $this->getParentClasses($reflection);

        /** @var string[] $tags */
        $tags = AttributeExtractor::extractParameters($classTags, parameter: 'name');
        $definition
            ->addTags($boundClassInfo?->getTags() ?? [])
            ->addTags($tags)
            ->setAliases($aliases)
            ->setInstanceOf($instanceOf);

        // TODO: write tests on interfaces which extend each other, will they be also written here
        $interfaces = $reflection->getInterfaceNames();

        $definition->setImplements($interfaces);
        foreach ($interfaces as $interface) {
            $interfaceReflection = new ReflectionClass($interface);

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

            /** @var string[] $tags */
            $tags = AttributeExtractor::extractParameters(
                $interfaceReflection->getAttributes(Tag::class),
                parameter: 'name',
            );

            $definition->addTags($tags);
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
}

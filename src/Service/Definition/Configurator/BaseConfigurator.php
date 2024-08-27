<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition\Configurator;

use Psr\Container\ContainerInterface;
use Temkaa\SimpleContainer\Container;
use Temkaa\SimpleContainer\Factory\Definition\InterfaceFactory;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Service\Definition\ConfiguratorInterface;

final readonly class BaseConfigurator implements ConfiguratorInterface
{
    public function __construct(
        private Container $container,
        private Bag $definitions,
    ) {
    }

    public function configure(): Bag
    {
        $this->addContainerInterfaceDefinition();
        $this->addContainerClassDefinition();

        return $this->definitions;
    }

    private function addContainerClassDefinition(): void
    {
        $this->definitions->add(
            (new ClassDefinition())
                ->setId($this->container::class)
                ->setAliases(['container'])
                ->setImplements([ContainerInterface::class])
                ->setInstance($this->container)
                ->setIsSingleton(true),
        );
    }

    private function addContainerInterfaceDefinition(): void
    {
        $this->definitions->add(
            InterfaceFactory::create(id: ContainerInterface::class, implementedById: $this->container::class),
        );
    }
}

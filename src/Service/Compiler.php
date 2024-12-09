<?php

declare(strict_types=1);

namespace Temkaa\Container\Service;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;
use Temkaa\Container\Container;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Definition\Bag;
use Temkaa\Container\Repository\DefinitionRepository;
use Temkaa\Container\Service\Definition\Configurator\BaseConfigurator;
use Temkaa\Container\Service\Definition\Configurator\DecoratorConfigurator;
use Temkaa\Container\Service\Definition\Configurator\InterfaceConfigurator;
use Temkaa\Container\Service\Definition\Configurator;
use Temkaa\Container\Service\Definition\Resolver;
use Temkaa\Container\Util\Flag;
use Temkaa\Container\Validator\Definition\DuplicatedAliasValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @internal
 */
final readonly class Compiler
{
    /**
     * @param Config[] $configs
     */
    public function __construct(
        private array $configs,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function compile(): ContainerInterface
    {
        Flag::clear();

        $definitions = new Bag();
        $container = new Container(new DefinitionRepository($definitions));

        $configurator = new DecoratorConfigurator(
            new InterfaceConfigurator(
                new Configurator(
                    new BaseConfigurator($container, $definitions),
                    $this->configs,
                ),
            ),
        );
        $definitions = $configurator->configure();

        (new DuplicatedAliasValidator())->validate($definitions);

        (new Resolver($definitions))->resolve();

        Flag::clear();

        return $container;
    }
}

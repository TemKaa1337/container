<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;
use Temkaa\SimpleContainer\Container;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Repository\DefinitionRepository;
use Temkaa\SimpleContainer\Service\Definition\Configurator;
use Temkaa\SimpleContainer\Service\Definition\Configurator\BaseConfigurator;
use Temkaa\SimpleContainer\Service\Definition\Configurator\DecoratorConfigurator;
use Temkaa\SimpleContainer\Service\Definition\Configurator\InterfaceConfigurator;
use Temkaa\SimpleContainer\Service\Definition\Resolver;
use Temkaa\SimpleContainer\Util\Flag;
use Temkaa\SimpleContainer\Validator\Definition\DuplicatedAliasValidator;

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

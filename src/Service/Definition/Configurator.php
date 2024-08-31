<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Attribute\Autowire;
use Temkaa\SimpleContainer\Attribute\Factory;
use Temkaa\SimpleContainer\Exception\CircularReferenceException;
use Temkaa\SimpleContainer\Exception\NonAutowirableClassException;
use Temkaa\SimpleContainer\Exception\UninstantiableEntryException;
use Temkaa\SimpleContainer\Factory\Config\ClassFactoryFactory as ConfigClassFactoryFactory;
use Temkaa\SimpleContainer\Factory\Definition\ClassFactoryFactory as DefinitionClassFactoryFactory;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Config\Decorator;
use Temkaa\SimpleContainer\Model\Config\Factory as ConfigFactory;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Model\Reference\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Util\Extractor\AttributeExtractor;
use Temkaa\SimpleContainer\Util\Extractor\ClassExtractor;
use Temkaa\SimpleContainer\Util\Flag;
use Temkaa\SimpleContainer\Validator\Argument\DecoratorValidator;
use Temkaa\SimpleContainer\Validator\Definition\FactoryValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @internal
 */
final class Configurator implements ConfiguratorInterface
{
    private readonly ArgumentConfigurator $argumentConfigurator;

    private readonly ClassExtractor $classExtractor;

    /**
     * @var Config[] $configs
     */
    private readonly array $configs;

    private readonly ConfiguratorInterface $configurator;

    private Bag $definitions;

    /**
     * @var string[]
     */
    private array $excludedClasses;

    private Config $resolvingConfig;

    /**
     * @param Config[] $configs
     */
    public function __construct(ConfiguratorInterface $configurator, array $configs)
    {
        $this->argumentConfigurator = new ArgumentConfigurator($this);
        $this->classExtractor = new ClassExtractor();
        $this->configs = $configs;
        $this->configurator = $configurator;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function configure(): Bag
    {
        $this->definitions = $this->configurator->configure();

        foreach ($this->configs as $config) {
            $this->resolvingConfig = $config;

            $includedClasses = $this->classExtractor->extract($config->getIncludedPaths());
            $this->excludedClasses = $this->classExtractor->extract($config->getExcludedPaths());

            foreach ($includedClasses as $class) {
                $this->configureDefinition($class, failIfUninstantiable: false);
            }
        }

        return $this->definitions;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param class-string $id
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function configureDefinition(string $id, bool $failIfUninstantiable = true): void
    {
        if ($this->definitions->has($id)) {
            return;
        }

        if (Flag::isToggled($id, group: 'definition')) {
            throw new CircularReferenceException($id, Flag::getToggled(group: 'definition'));
        }

        Flag::toggle($id, group: 'definition');

        $reflection = new ReflectionClass($id);
        if ($reflection->isInternal()) {
            throw new UninstantiableEntryException(sprintf('Cannot resolve internal entry "%s".', $id));
        }

        $factoryAttributes = $reflection->getAttributes(Factory::class);
        $classConfigFactory = $this->resolvingConfig->getBoundedClass($id)?->getFactory();

        $factory = match (true) {
            (bool) $factoryAttributes  => ConfigClassFactoryFactory::createFromAttribute(
                AttributeExtractor::extract($factoryAttributes, index: 0),
            ),
            (bool) $classConfigFactory => $classConfigFactory,
            default                    => null,
        };

        if ($factory) {
            (new FactoryValidator())->validate($factory, $id);
        }

        if (!$factory && !$reflection->isInstantiable()) {
            Flag::untoggle($id, group: 'definition');

            if (!$failIfUninstantiable) {
                return;
            }

            throw new UninstantiableEntryException(sprintf('Cannot instantiate entry with id "%s".', $id));
        }

        if (in_array($id, $this->excludedClasses, strict: true)) {
            Flag::untoggle($id, group: 'definition');

            throw new NonAutowirableClassException(
                sprintf('Cannot autowire class "%s" as it is in "exclude" config parameter.', $id),
            );
        }

        $autowireTags = $reflection->getAttributes(Autowire::class);

        $isNonAutowirable = AttributeExtractor::hasParameterByValue($autowireTags, parameter: 'load', value: false);
        if ($isNonAutowirable) {
            Flag::untoggle($id, group: 'definition');

            if (!$failIfUninstantiable) {
                return;
            }

            throw new NonAutowirableClassException(
                sprintf('Class "%s" has NonAutowirable attribute and cannot be autowired.', $id),
            );
        }

        $definition = (new ClassDefinition())->setId($id);

        if ($autowireTags) {
            $isSingleton = AttributeExtractor::extract($autowireTags, index: 0)->singleton;
            $definition->setIsSingleton($isSingleton);
        }

        if ($boundClassInfo = $this->resolvingConfig->getBoundedClass($id)) {
            $definition->setIsSingleton($boundClassInfo->isSingleton());
        }

        (new Populator())->populate($definition, $reflection, $this->resolvingConfig, $this->definitions);

        $constructor = $reflection->getConstructor();
        if (!$constructor && !$factory) {
            Flag::untoggle($id, group: 'definition');

            $this->definitions->add($definition);

            return;
        }

        $this->configureArguments($constructor?->getParameters() ?? [], $definition, $factory);

        $this->definitions->add($definition);

        Flag::untoggle($id, group: 'definition');
    }

    private function configureArguments(array $arguments, ClassDefinition $definition, ?ConfigFactory $factory): void
    {
        $decorates = $definition->getDecorates();

        if ($factory) {
            $reflection = new ReflectionClass($factory->getId());
            $methodReflection = $reflection->getMethod($factory->getMethod());
            if (!$methodReflection->isStatic()) {
                $this->configureDefinition($factory->getId());

                /** @var ClassDefinition $factoryDefinition */
                $factoryDefinition = $this->definitions->get($factory->getId());
            } else {
                $factoryDefinition = null;
            }

            (new DecoratorValidator())->validate($decorates, $methodReflection->getParameters(), $definition->getId());

            if ($decorates && count($methodReflection->getParameters()) === 1) {
                $factoryArguments = [
                    new DecoratorReference(
                        $decorates->getId(),
                        $decorates->getPriority(),
                        $decorates->getSignature(),
                    ),
                ];
            } else {
                $factoryArguments = [];
                foreach ($methodReflection->getParameters() as $argument) {
                    $factoryArguments[] = $this->argumentConfigurator->configureArgument(
                        $this->resolvingConfig,
                        $this->definitions,
                        $argument,
                        $factoryDefinition,
                        $factory->getId(),
                        $factory,
                        $definition->getDecorates(),
                    );
                }
            }

            $factory = DefinitionClassFactoryFactory::create(
                $factory->getId(),
                $factory->getMethod(),
                $factoryArguments,
                $methodReflection->isStatic(),
            );

            $definition->setFactory($factory);
        } else if ($decorates && count($arguments) === 1) {
            (new DecoratorValidator())->validate($decorates, $arguments, $definition->getId());

            $definition->addArgument(
                new DecoratorReference(
                    $decorates->getId(),
                    $decorates->getPriority(),
                    $decorates->getSignature(),
                ),
            );
        } else {
            (new DecoratorValidator())->validate($decorates, $arguments, $definition->getId());

            foreach ($arguments as $argument) {
                $definition->addArgument(
                    $this->argumentConfigurator->configureArgument(
                        $this->resolvingConfig,
                        $this->definitions,
                        $argument,
                        $definition,
                        $definition->getId(),
                        factory: null,
                        decorates: $definition->getDecorates(),
                    ),
                );
            }
        }
    }
}

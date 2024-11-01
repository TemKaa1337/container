<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\Container\Attribute\Autowire;
use Temkaa\Container\Attribute\Bind\Required;
use Temkaa\Container\Attribute\Factory;
use Temkaa\Container\Exception\CircularReferenceException;
use Temkaa\Container\Exception\NonAutowirableClassException;
use Temkaa\Container\Exception\UninstantiableEntryException;
use Temkaa\Container\Factory\Config\ClassFactoryFactory as ConfigClassFactoryFactory;
use Temkaa\Container\Factory\Definition\ClassFactoryFactory as DefinitionClassFactoryFactory;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Config\Factory as ConfigFactory;
use Temkaa\Container\Model\Definition\Bag;
use Temkaa\Container\Model\Definition\ClassDefinition;
use Temkaa\Container\Util\Extractor\AttributeExtractor;
use Temkaa\Container\Util\Extractor\ClassExtractor;
use Temkaa\Container\Util\Flag;
use Temkaa\Container\Validator\Definition\FactoryValidator;
use Temkaa\Container\Validator\Definition\Method\RequiredMethodCallValidator;

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
            (bool) $classConfigFactory => $classConfigFactory,
            (bool) $factoryAttributes  => ConfigClassFactoryFactory::createFromAttribute(
                AttributeExtractor::extract($factoryAttributes, index: 0),
            ),
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

            if (!$failIfUninstantiable) {
                return;
            }

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

        $this->configureRequiredMethodCalls($definition);

        $constructor = $reflection->getConstructor();
        if (!$constructor && !$factory) {
            Flag::untoggle($id, group: 'definition');

            $this->definitions->add($definition);

            return;
        }

        if ($factory) {
            $this->configureFactory($definition, $factory);
        } else {
            $definition->setArguments(
                $this->argumentConfigurator->configure(
                    $this->resolvingConfig,
                    $this->definitions,
                    $constructor->getParameters(),
                    $definition->getId(),
                    factory: null,
                    decorates: $definition->getDecorates(),
                ),
            );
        }

        $this->definitions->add($definition);

        Flag::untoggle($id, group: 'definition');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function configureFactory(ClassDefinition $definition, ConfigFactory $factory): void
    {
        $reflection = new ReflectionClass($factory->getId());
        $methodReflection = $reflection->getMethod($factory->getMethod());
        if (!$methodReflection->isStatic()) {
            $this->configureDefinition($factory->getId());
        }

        $factoryArguments = $this->argumentConfigurator->configure(
            $this->resolvingConfig,
            $this->definitions,
            $methodReflection->getParameters(),
            $factory->getId(),
            $factory,
            $definition->getDecorates(),
        );

        $factory = DefinitionClassFactoryFactory::create(
            $factory->getId(),
            $factory->getMethod(),
            $factoryArguments,
            $methodReflection->isStatic(),
        );

        $definition->setFactory($factory);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function configureRequiredMethodCalls(ClassDefinition $definition): void
    {
        $reflection = new ReflectionClass($definition->getId());

        $definitionConfig = $this->resolvingConfig->getBoundedClass($definition->getId());

        (new RequiredMethodCallValidator())->validate($definitionConfig, $reflection);

        $requiredMethodCalls = $definitionConfig?->getMethodCalls() ?? [];

        foreach ($reflection->getMethods() as $method) {
            if (!$method->getAttributes(Required::class)) {
                continue;
            }

            $requiredMethodCalls[] = $method->getName();
        }

        $requiredMethodCalls = array_values(array_unique($requiredMethodCalls));

        $resolvedMethodCalls = [];
        foreach ($requiredMethodCalls as $requiredMethodCall) {
            $resolvedMethodCalls[$requiredMethodCall] = $this->argumentConfigurator->configure(
                $this->resolvingConfig,
                $this->definitions,
                $reflection->getMethod($requiredMethodCall)->getParameters(),
                $definition->getId(),
                factory: null,
                decorates: $definition->getDecorates(),
            );
        }

        $definition->setRequiredMethodCalls($resolvedMethodCalls);
    }
}

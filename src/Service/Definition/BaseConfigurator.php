<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Attribute\Alias;
use Temkaa\SimpleContainer\Attribute\Autowire;
use Temkaa\SimpleContainer\Attribute\Decorates;
use Temkaa\SimpleContainer\Attribute\Tag;
use Temkaa\SimpleContainer\Exception\CircularReferenceException;
use Temkaa\SimpleContainer\Exception\NonAutowirableClassException;
use Temkaa\SimpleContainer\Exception\UninstantiableEntryException;
use Temkaa\SimpleContainer\Factory\Definition\DecoratorFactory;
use Temkaa\SimpleContainer\Factory\Definition\InterfaceFactory;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Model\Reference\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Util\Extractor\AttributeExtractor;
use Temkaa\SimpleContainer\Util\Extractor\ClassExtractor;
use Temkaa\SimpleContainer\Util\Flag;
use Temkaa\SimpleContainer\Validator\Argument\DecoratorValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @internal
 */
final class BaseConfigurator implements ConfiguratorInterface
{
    private ArgumentConfigurator $argumentConfigurator;

    private ClassExtractor $classExtractor;

    /**
     * @var Config[] $configs
     */
    private readonly array $configs;

    private Bag $definitions;

    /**
     * @var string[]
     */
    private array $excludedClasses;

    private Config $resolvingConfig;

    /**
     * @param Config[] $configs
     */
    public function __construct(array $configs)
    {
        $this->argumentConfigurator = new ArgumentConfigurator($this);
        $this->classExtractor = new ClassExtractor();
        $this->configs = $configs;
        $this->definitions = new Bag();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function configure(): Bag
    {
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

        if (!$reflection->isInstantiable()) {
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

        $definition = (new ClassDefinition())->setId($id);

        if ($autowireTags = $reflection->getAttributes(Autowire::class)) {
            $isSingleton = AttributeExtractor::extract($autowireTags, index: 0)->singleton;
            $definition->setIsSingleton($isSingleton);
        }

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

        if ($boundClassInfo = $this->resolvingConfig->getBoundedClass($id)) {
            $definition->setIsSingleton($boundClassInfo->isSingleton());
        }

        $this->populateDefinition($definition, $reflection);

        if (!$constructor = $reflection->getConstructor()) {
            Flag::untoggle($id, group: 'definition');

            $this->definitions->add($definition);

            return;
        }

        $arguments = $constructor->getParameters();
        $decorates = $definition->getDecorates();

        (new DecoratorValidator())->validate($decorates, $arguments, $definition->getId());

        if ($decorates && count($arguments) === 1) {
            $definition->addArgument(
                new DecoratorReference(
                    $decorates->getId(),
                    $decorates->getPriority(),
                    $decorates->getSignature(),
                ),
            );
        } else {
            foreach ($arguments as $argument) {
                $definition->addArgument(
                    $this->argumentConfigurator->configureArgument(
                        $this->resolvingConfig,
                        $this->definitions,
                        $argument,
                        $definition,
                        $id,
                    ),
                );
            }
        }

        $this->definitions->add($definition);

        Flag::untoggle($id, group: 'definition');
    }

    private function populateDefinition(ClassDefinition $definition, ReflectionClass $reflection): void
    {
        $classTags = $reflection->getAttributes(Tag::class);
        $classAliases = $reflection->getAttributes(Alias::class);

        $boundClassInfo = $this->resolvingConfig->getBoundedClass($reflection->getName());

        /** @var string[] $aliases */
        $aliases = array_merge(
            AttributeExtractor::extractParameters($classAliases, parameter: 'name'),
            $this->resolvingConfig->getBoundedClass($definition->getId())?->getAliases() ?? [],
        );

        $definition
            ->addTags($boundClassInfo?->getTags() ?? [])
            ->addTags(AttributeExtractor::extractParameters($classTags, parameter: 'name'))
            ->setAliases($aliases);

        $interfaces = $reflection->getInterfaces();
        $definition->setImplements(array_keys($interfaces));
        foreach ($interfaces as $interface) {
            $interfaceName = $interface->getName();

            if (
                $this->resolvingConfig->hasBoundInterface($interfaceName)
                && $this->resolvingConfig->getBoundInterfaceImplementation($interfaceName) === $reflection->getName()
            ) {
                $this->definitions->add(
                    InterfaceFactory::create(
                        $interfaceName,
                        implementedById: $reflection->getName(),
                    ),
                );
            }

            $interfaceTags = $interface->getAttributes(Tag::class);
            $definition->addTags(AttributeExtractor::extractParameters($interfaceTags, parameter: 'name'));
        }

        if ($decorates = $boundClassInfo?->getDecorates()) {
            $definition->setDecorates($decorates);
        } else if ($decoratesAttribute = $reflection->getAttributes(Decorates::class)) {
            $decoratesAttribute = AttributeExtractor::extract($decoratesAttribute, index: 0);

            $definition->setDecorates(DecoratorFactory::createFromAttribute($decoratesAttribute));
        }
    }
}

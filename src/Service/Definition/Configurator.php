<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\Container\Attribute\Autowire;
use Temkaa\Container\Attribute\Bind\Required;
use Temkaa\Container\Attribute\Factory;
use Temkaa\Container\Debug\PerformanceChecker;
use Temkaa\Container\Exception\CircularReferenceException;
use Temkaa\Container\Exception\NonAutowirableClassException;
use Temkaa\Container\Exception\UninstantiableEntryException;
use Temkaa\Container\Factory\Config\ClassFactoryFactory as ConfigClassFactoryFactory;
use Temkaa\Container\Factory\Definition\ClassFactoryFactory as DefinitionClassFactoryFactory;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Config\Factory as ConfigFactory;
use Temkaa\Container\Model\Definition\Bag;
use Temkaa\Container\Model\Definition\ClassDefinition;
use Temkaa\Container\Service\CachingReflector;
use Temkaa\Container\Util\Extractor\AttributeExtractor;
use Temkaa\Container\Util\Extractor\ClassExtractor;
use Temkaa\Container\Util\Extractor\ClassExtractorRefactored;
use Temkaa\Container\Util\Extractor\UniqueDirectoryExtractor;
use Temkaa\Container\Util\Flag;
use Temkaa\Container\Validator\Definition\FactoryValidator;
use Temkaa\Container\Validator\Definition\Method\RequiredMethodCallValidator;
use function array_merge;
use function array_unique;
use function array_values;
use function in_array;
use function sprintf;
use function var_dump;

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

    private PerformanceChecker $performanceChecker;

    private int $reflectionCalls = 0;

    private Config $resolvingConfig;

    /**
     * @param Config[] $configs
     */
    public function __construct(
        ConfiguratorInterface $configurator,
        array $configs,
        PerformanceChecker $performanceChecker,
    ) {
        $this->argumentConfigurator = new ArgumentConfigurator($this, $performanceChecker);
        $this->classExtractor = new ClassExtractor($performanceChecker);
        $this->configs = $configs;
        $this->configurator = $configurator;
        $this->performanceChecker = $performanceChecker;
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

            $this->performanceChecker->start('extract dirs');
            $uniquePaths = (new UniqueDirectoryExtractor())->extract(
                array_merge($config->getIncludedPaths(), $config->getExcludedPaths()),
            );
            $this->performanceChecker->end('extract dirs');
            $this->performanceChecker->start('get classes');
            [$includedClasses, $excludedClasses] = (new ClassExtractorRefactored($this->performanceChecker))->extract(
                $uniquePaths,
                $config->getExcludedPaths(),
            );
            $this->performanceChecker->end('get classes');
            $this->excludedClasses = $excludedClasses;

            $this->performanceChecker->print('extract dirs');
            $this->performanceChecker->print('get classes');
            $this->performanceChecker->print('include & exclude -> file_get_contents');
            $this->performanceChecker->print('include & exclude -> token_get_all');
            $this->performanceChecker->print('include & exclude -> token extraction');
            $this->performanceChecker->print('determine section');
            $this->performanceChecker->print('class itself extraction');
            // try {
            //     $this->performanceChecker->print('include & exclude -> pathinfo');
            // } catch (Throwable $throwable) {
            //     echo "no data for include & exclude -> pathinfo\n";
            // }

            $this->performanceChecker->start('configure definitions');
            foreach ($includedClasses as $class) {
                $this->configureDefinition($class, failIfUninstantiable: false, isRoot: true);
            }
            $this->performanceChecker->end('configure definitions');
            $this->performanceChecker->print('configure definitions');

            $this->performanceChecker->print('configure definitions -> new ReflectionClass');
            $this->performanceChecker->print('configure definitions -> factory stuff');
            $this->performanceChecker->print('configure definitions -> populator');
            $this->performanceChecker->print('configure ROOT definitions -> configure arguments');
            $this->performanceChecker->print('configure NON ROOT definitions -> configure arguments');
            $this->performanceChecker->print('configure definitions -> other');
            $this->performanceChecker->print('configure definitions -> required method calls');
            // var_dump('reflection calls: '.$this->reflectionCalls);
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
    public function configureDefinition(string $id, bool $failIfUninstantiable = true, bool $isRoot = false): void
    {
        if ($this->definitions->has($id)) {
            return;
        }

        if (Flag::isToggled($id, group: 'definition')) {
            throw new CircularReferenceException($id, Flag::getToggled(group: 'definition'));
        }

        Flag::toggle($id, group: 'definition');

        $this->performanceChecker->start('configure definitions -> new ReflectionClass');
        $reflection = new ReflectionClass($id);
        $this->reflectionCalls++;
        $this->performanceChecker->end('configure definitions -> new ReflectionClass');
        if ($reflection->isInternal()) {
            throw new UninstantiableEntryException(sprintf('Cannot resolve internal entry "%s".', $id));
        }

        if ($isRoot) {
            $this->performanceChecker->start('configure definitions -> factory stuff');
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
        if ($isRoot) {
            $this->performanceChecker->end('configure definitions -> factory stuff');
        }

        if ($isRoot) {
            $this->performanceChecker->start('configure definitions -> other');
        }
        if (!$factory && !$reflection->isInstantiable()) {
            Flag::untoggle($id, group: 'definition');

            if ($isRoot) {
                $this->performanceChecker->end('configure definitions -> other');
            }
            if (!$failIfUninstantiable) {
                return;
            }

            throw new UninstantiableEntryException(sprintf('Cannot instantiate entry with id "%s".', $id));
        }

        if (in_array($id, $this->excludedClasses, strict: true)) {
            Flag::untoggle($id, group: 'definition');

            if ($isRoot) {
                $this->performanceChecker->end('configure definitions -> other');
            }
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

            if ($isRoot) {
                $this->performanceChecker->end('configure definitions -> other');
            }
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
        if ($isRoot) {
            $this->performanceChecker->end('configure definitions -> other');
        }

        if ($isRoot) {
            $this->performanceChecker->start('configure definitions -> populator');
        }
        (new Populator())->populate($definition, $reflection, $this->resolvingConfig, $this->definitions);
        if ($isRoot) {
            $this->performanceChecker->end('configure definitions -> populator');
        }
        if ($isRoot) {
            $this->performanceChecker->start('configure definitions -> required method calls');
        }
        $this->configureRequiredMethodCalls($definition);
        if ($isRoot) {
            $this->performanceChecker->end('configure definitions -> required method calls');
        }

        $constructor = $reflection->getConstructor();
        if (!$constructor && !$factory) {
            Flag::untoggle($id, group: 'definition');

            $this->definitions->add($definition);

            return;
        }

        if ($factory) {
            $this->configureFactory($definition, $factory);
        } else {
            if ($isRoot) {
                $this->performanceChecker->start('configure ROOT definitions -> configure arguments');
            } else {
                $this->performanceChecker->start('configure NON ROOT definitions -> configure arguments');
            }
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
            if ($isRoot) {
                $this->performanceChecker->end('configure ROOT definitions -> configure arguments');
            } else {
                $this->performanceChecker->end('configure NON ROOT definitions -> configure arguments');
            }
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

        $definitionClassFactory = DefinitionClassFactoryFactory::create(
            $factory->getId(),
            $factory->getMethod(),
            $factoryArguments,
            $methodReflection->isStatic(),
        );

        $definition->setFactory($definitionClassFactory);
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

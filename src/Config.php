<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer;

use Psr\Container\ContainerExceptionInterface;
use Temkaa\SimpleContainer\Attribute\NonAutowirable;
use Temkaa\SimpleContainer\Exception\EntryNotFoundException;
use Temkaa\SimpleContainer\Validator\Config\ClassBindingNodeValidator;
use Temkaa\SimpleContainer\Validator\Config\ConfigDirectoryNodeValidator;
use Temkaa\SimpleContainer\Validator\Config\EnvValidator;
use Temkaa\SimpleContainer\Validator\Config\InterfaceBindingNodeValidator;
use Temkaa\SimpleContainer\Validator\Config\ServicesNodeValidator;

#[NonAutowirable]
final class Config
{
    /**
     * @var array<class-string, array<string, string>>
     */
    private array $classBoundVariables = [];

    private readonly ClassExtractor $classExtractor;

    /**
     * @var array<class-string, string[]>
     */
    private array $classTags = [];

    /**
     * @var class-string[]
     */
    private array $excludeClasses = [];

    private readonly ExpressionParser $expressionParser;

    /**
     * @var class-string[]
     */
    private array $includeClasses = [];

    /**
     * @var array<class-string, class-string>
     */
    private array $interfaceBindings = [];

    public function __construct(array $config, array $env)
    {
        $this->classExtractor = new ClassExtractor();
        $this->expressionParser = new ExpressionParser($env);

        $this->load($config, $env);
    }

    /**
     * @return class-string[]
     */
    public function getAutowiredClasses(): array
    {
        return array_diff($this->includeClasses, $this->excludeClasses);
    }

    /**
     * @return array<string, string>
     */
    public function getClassBoundVariables(string $class): array
    {
        return $this->classBoundVariables[$class] ?? [];
    }

    /**
     * @return string[]
     */
    public function getClassTags(string $class): array
    {
        return $this->classTags[$class] ?? [];
    }

    /**
     * @param class-string $interface
     *
     * @return class-string
     * @throws ContainerExceptionInterface
     */
    public function getInterfaceImplementation(string $interface): string
    {
        if (!isset($this->interfaceBindings[$interface])) {
            throw new EntryNotFoundException($interface);
        }

        return $this->interfaceBindings[$interface];
    }

    /**
     * @return class-string[]
     */
    public function getNonAutowiredClasses(): array
    {
        return $this->excludeClasses;
    }

    /**
     * @param class-string $interface
     */
    public function hasImplementation(string $interface): bool
    {
        return isset($this->interfaceBindings[$interface]);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    private function load(array $config, array $env): void
    {
        (new ConfigDirectoryNodeValidator())->validate($config);
        (new EnvValidator())->validate($env);
        (new InterfaceBindingNodeValidator())->validate($config);
        (new ClassBindingNodeValidator())->validate($config, $env);
        (new ServicesNodeValidator())->validate($config, $config['config_dir']);

        $this->parseInterfaceBindings($config);
        $this->parseClassBindings($config);
        $this->parseServices($config, $config['config_dir']);
    }

    private function parseClassBindings(array $config): void
    {
        if (!isset($config['class_bindings'])) {
            return;
        }

        foreach ($config['class_bindings'] as $className => $variableBindings) {
            $variables = [];
            foreach ($variableBindings['bind'] ?? [] as $variableName => $variableValue) {
                $variableValue = $this->expressionParser->parse($variableValue);

                $variables[str_replace('$', '', $variableName)] = $variableValue;
            }

            $this->classBoundVariables[$className] = $variables;
            $this->classTags[$className] = $variableBindings['tags'] ?? [];
        }
    }

    private function parseInterfaceBindings(array $config): void
    {
        if (!isset($config['interface_bindings'])) {
            return;
        }

        foreach ($config['interface_bindings'] as $interfaceName => $className) {
            $this->interfaceBindings[$interfaceName] = $className;
        }
    }

    private function parseServices(array $config, string $configDir): void
    {
        if (!isset($config['services'])) {
            return;
        }

        $includeAutowireClasses = [];
        $excludeAutowireClasses = [];

        foreach ($config['services']['include'] ?? [] as $classPath) {
            $dir = realpath($configDir.'/'.$classPath);
            $includeAutowireClasses = [...$includeAutowireClasses, ...$this->classExtractor->extract($dir)];
        }

        foreach ($config['services']['exclude'] ?? [] as $classPath) {
            $dir = realpath($configDir.'/'.$classPath);
            $excludeAutowireClasses = [...$excludeAutowireClasses, ...$this->classExtractor->extract($dir)];
        }

        $this->includeClasses = $includeAutowireClasses;
        $this->excludeClasses = $excludeAutowireClasses;
    }
}

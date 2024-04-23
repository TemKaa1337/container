<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Factory\Config;

use SplFileInfo;
use Temkaa\SimpleContainer\Model\Container\Config;
use Temkaa\SimpleContainer\Util\ClassExtractor;
use Temkaa\SimpleContainer\Util\ExpressionParser;

final readonly class ConfigFactory
{
    public function __construct(
        private array $config,
        private SplFileInfo $configFile,
        private ClassExtractor $classExtractor = new ClassExtractor(),
        private ExpressionParser $expressionParser = new ExpressionParser(),
    ) {
    }

    public function create(): Config
    {
        $classBoundVariables = $this->getClassBoundVariables();
        $classTags = $this->getClassTags();
        $interfaceBindings = $this->parseInterfaceBindings();
        $includedClasses = $this->getIncludeClasses();
        $excludedClasses = $this->getExcludedClasses();

        return (new Config())
            ->setClassBoundVariables($classBoundVariables)
            ->setClassTags($classTags)
            ->setInterfaceImplementations($interfaceBindings)
            ->setIncludedClasses(array_diff($includedClasses, $excludedClasses))
            ->setExcludedClasses($excludedClasses);
    }

    /**
     * @psalm-suppress InvalidReturnType,InvalidReturnStatement
     *
     * @return array<class-string, array<string, string>>
     */
    private function getClassBoundVariables(): array
    {
        $variables = [];

        foreach ($this->config['class_bindings'] ?? [] as $className => $variableBindings) {
            $classVariables = [];

            foreach ($variableBindings['bind'] ?? [] as $variableName => $variableValue) {
                $variableName = str_replace('$', '', $variableName);
                $variableValue = $this->expressionParser->parse($variableValue);

                $classVariables[$variableName] = $variableValue;
            }

            $variables[$className] = $classVariables;
        }

        return $variables;
    }

    /**
     * @return array<class-string, string[]>
     */
    private function getClassTags(): array
    {
        $tags = [];
        foreach ($this->config['class_bindings'] ?? [] as $className => $variableBindings) {
            $tags[$className] = $variableBindings['tags'] ?? [];
        }

        return $tags;
    }

    /**
     * @return class-string[]
     */
    private function getExcludedClasses(): array
    {
        $excludeAutowireClasses = [];
        foreach ($this->config['services']['exclude'] ?? [] as $classPath) {
            $classFile = new SplFileInfo(sprintf('%s/%s', $this->configFile->getPath(), $classPath));

            $excludeAutowireClasses[] = $this->classExtractor->extract($classFile->getRealPath());
        }

        return array_merge(...$excludeAutowireClasses);
    }

    /**
     * @return class-string[]
     */
    private function getIncludeClasses(): array
    {
        $includeAutowireClasses = [];
        foreach ($this->config['services']['include'] ?? [] as $classPath) {
            $classFile = new SplFileInfo(sprintf('%s/%s', $this->configFile->getPath(), $classPath));

            $includeAutowireClasses[] = $this->classExtractor->extract($classFile->getRealPath());
        }

        return array_merge(...$includeAutowireClasses);
    }

    /**
     * @return  array<class-string, class-string>
     */
    private function parseInterfaceBindings(): array
    {
        return $this->config['interface_bindings'] ?? [];
    }
}

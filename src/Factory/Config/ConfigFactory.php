<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Factory\Config;

use SplFileInfo;
use Temkaa\SimpleContainer\Enum\Config\Structure;
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
        $globalBoundVariables = $this->getGlobalBoundVariables();
        $classBoundVariables = $this->getClassBoundVariables();
        $classTags = $this->getClassTags();
        $interfaceBindings = $this->parseInterfaceBindings();
        $includedClasses = $this->getIncludedClasses();
        $excludedClasses = $this->getExcludedClasses();
        $classSingletons = $this->getClassSingletons();

        return (new Config())
            ->setGlobalBoundVariables($globalBoundVariables)
            ->setClassBoundVariables($classBoundVariables)
            ->setClassTags($classTags)
            ->setInterfaceImplementations($interfaceBindings)
            ->setIncludedClasses(array_diff($includedClasses, $excludedClasses))
            ->setExcludedClasses($excludedClasses)
            ->setClassSingletons($classSingletons);
    }

    /**
     * @psalm-suppress InvalidReturnType,InvalidReturnStatement
     *
     * @return array<class-string, array<string, string>>
     */
    private function getClassBoundVariables(): array
    {
        $variables = [];
        foreach ($this->config[Structure::Services->value] ?? [] as $nodeName => $nodeValue) {
            if (!class_exists($nodeName)) {
                continue;
            }

            $variables[$nodeName] = $this->parseVariables($nodeValue[Structure::Bind->value] ?? []);
        }

        return $variables;
    }

    /**
     * @psalm-suppress InvalidReturnType,InvalidReturnStatement
     *
     * @return array<class-string, bool>
     */
    private function getClassSingletons(): array
    {
        /** @var array<class-string, bool> $singletons */
        $singletons = [];
        foreach ($this->config[Structure::Services->value] ?? [] as $nodeName => $nodeValue) {
            if (!class_exists($nodeName)) {
                continue;
            }

            $isSingleton = $nodeValue[Structure::Singleton->value] ?? null;
            if ($isSingleton === null) {
                continue;
            }

            $singletons[$nodeName] = $isSingleton;
        }

        return $singletons;
    }

    /**
     * @return array<class-string, string[]>
     */
    private function getClassTags(): array
    {
        $tags = [];
        foreach ($this->config[Structure::Services->value] ?? [] as $nodeName => $nodeValue) {
            if (!class_exists($nodeName)) {
                continue;
            }

            $tags[$nodeName] = $nodeValue[Structure::Tags->value] ?? [];
        }

        return $tags;
    }

    /**
     * @return class-string[]
     */
    private function getExcludedClasses(): array
    {
        $excludeAutowireClasses = [];
        foreach ($this->config[Structure::Services->value][Structure::Exclude->value] ?? [] as $classPath) {
            $classFile = new SplFileInfo(sprintf('%s/%s', $this->configFile->getPath(), $classPath));

            $excludeAutowireClasses[] = $this->classExtractor->extract($classFile->getRealPath());
        }

        return array_merge(...$excludeAutowireClasses);
    }

    /**
     * @return array<string, string>
     */
    private function getGlobalBoundVariables(): array
    {
        return $this->parseVariables($this->config[Structure::Services->value][Structure::Bind->value] ?? []);
    }

    /**
     * @return class-string[]
     */
    private function getIncludedClasses(): array
    {
        $includeAutowireClasses = [];
        foreach ($this->config[Structure::Services->value][Structure::Include->value] ?? [] as $classPath) {
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
        $interfaceBindings = [];
        foreach ($this->config[Structure::Services->value] ?? [] as $nodeName => $nodeValue) {
            if (!interface_exists($nodeName)) {
                continue;
            }

            $interfaceBindings[$nodeName] = $nodeValue;
        }

        return $interfaceBindings;
    }

    /**
     * @param array<string, string> $variablesInfo
     *
     * @return array<string, string>
     */
    private function parseVariables(array $variablesInfo): array
    {
        $variables = [];

        foreach ($variablesInfo as $variableName => $variableValue) {
            $variableName = str_replace('$', '', $variableName);

            $variables[$variableName] = $this->expressionParser->parse($variableValue);
        }

        return $variables;
    }
}

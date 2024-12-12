<?php

declare(strict_types=1);

namespace Temkaa\Container\Util\Extractor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Temkaa\Container\Debug\PerformanceChecker;
use function array_merge;
use function file_get_contents;
use function glob;
use function in_array;
use function is_file;
use function is_string;
use function ltrim;
use function realpath;
use function str_starts_with;
use function token_get_all;
use function var_dump;
use const T_CLASS;
use const T_COMMENT;
use const T_DOC_COMMENT;
use const T_DOUBLE_COLON;
use const T_INTERFACE;
use const T_NS_SEPARATOR;
use const T_STRING;
use const T_WHITESPACE;

/**
 * @internal
 */
final class ClassExtractorRefactored
{
    private const array NAMESPACE_TOKENS = [T_STRING => true, T_NS_SEPARATOR => true, T_NAME_QUALIFIED => true];

    private const array SKIP_TOKENS = [T_WHITESPACE => true, T_DOC_COMMENT => true, T_COMMENT => true];

    public function __construct(
        private readonly PerformanceChecker $performanceChecker = new PerformanceChecker(),
    ) {
    }

    /**
     * @param string[]|string $paths
     *
     * @return array{0: list<class-string>, 1: list<class-string>}
     */
    public function extract(array|string $paths, array $excludedPaths): array
    {
        $paths = is_string($paths) ? [$paths] : $paths;

        /** @var list<list<class-string>> $includedClasses */
        $includedClasses = [];
        /** @var list<list<class-string>> $excludedClasses */
        $excludedClasses = [];

        foreach ($paths as $path) {
            $this->performanceChecker->start('class itself extraction');
            $extractedClasses = $this->extractFromPath($path);
            $this->performanceChecker->end('class itself extraction');

            $this->performanceChecker->start('determine section');
            foreach ($extractedClasses as $classPath => $class) {
                if (str_starts_with($class, 'Tests\Fixture\Benchmark\Mode')) {
                    $test = 'test';
                }
                $classPath = realpath($classPath);
                $isExcluded = false;
                foreach ($excludedPaths as $excludedPath) {
                    if (str_starts_with($classPath, realpath($excludedPath))) {
                        $isExcluded = true;
                        break;
                    }
                }

                if ($isExcluded) {
                    $excludedClasses[] = $class;
                } else {
                    $includedClasses[] = $class;
                }
            }
            $this->performanceChecker->end('determine section');
        }

        return [$includedClasses, $excludedClasses];
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     *
     * @return class-string|null
     */
    private function extractFromFile(string $path): ?string
    {
        $this->performanceChecker->start('include & exclude -> file_get_contents');
        $contents = file_get_contents($path);
        $this->performanceChecker->end('include & exclude -> file_get_contents');

        $this->performanceChecker->start('include & exclude -> token_get_all');
        $tokens = token_get_all($contents);
        $this->performanceChecker->end('include & exclude -> token_get_all');

        $this->performanceChecker->start('include & exclude -> token extraction');

        $namespace = '';
        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];

            if (!isset($token[1])) {
                continue;
            }

            $class = '';

            switch ($token[0]) {
                case T_NAMESPACE:
                    $namespace = '';
                    while (isset($tokens[++$i][1])) {
                        if (isset(self::NAMESPACE_TOKENS[$tokens[$i][0]])) {
                            $namespace .= $tokens[$i][1];
                        }
                    }

                    $namespace .= '\\';
                    break;
                case T_CLASS:
                case T_INTERFACE:
                case T_TRAIT:
                    for ($j = $i - 1; $j > 0; --$j) {
                        if (!isset($tokens[$j][1])) {
                            break;
                        }

                        if ($tokens[$j][0] === T_DOUBLE_COLON) {
                            break 2;
                        }

                        if (!isset(self::SKIP_TOKENS[$tokens[$j][0]])) {
                            break;
                        }
                    }

                    while (isset($tokens[++$i][1])) {
                        $t = $tokens[$i];
                        if (T_STRING === $t[0]) {
                            $class .= $t[1];
                        } elseif ($class !== '' && $t[0] === T_WHITESPACE) {
                            break;
                        }
                    }

                    $this->performanceChecker->end('include & exclude -> token extraction');

                    return ltrim($namespace.$class, '\\');
                default:
                    break;
            }
        }

        return null;
    }

    /**
     * @return array<string, class-string>
     */
    private function extractFromPath(string $path): array
    {
        if (is_file($path)) {
            $class = $this->extractFromFile($path);

            return $class !== null ? [$path => $class] : [];
        }

        $recursiveDirectoryIterator = new RecursiveDirectoryIterator($path);
        $wrappedDirectoryIterator = new RecursiveIteratorIterator($recursiveDirectoryIterator);
        $regexIterator = new RegexIterator($wrappedDirectoryIterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        /** @var array<string, class-string> $classes */
        $classes = [];
        foreach ($regexIterator as $file) {
            // var_dump($file);
            // die();
            $path = $file[0];

            $className = $this->extractFromFile($path);
            if ($className !== null) {
                $classes[$path] = $className;
            }
        }

        return $classes;
    }
}

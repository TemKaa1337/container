<?php

declare(strict_types=1);

namespace Temkaa\Container\Util\Extractor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use function file_get_contents;
use function is_file;
use function is_string;
use function ltrim;
use function str_starts_with;
use function token_get_all;
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
final class ClassExtractor
{
    private const array NAMESPACE_TOKENS = [T_STRING => true, T_NS_SEPARATOR => true, T_NAME_QUALIFIED => true];
    private const array SKIP_TOKENS = [T_WHITESPACE => true, T_DOC_COMMENT => true, T_COMMENT => true];

    /**
     * @param string[]|string $paths
     * @param list<string>    $excludedPaths
     *
     * @return array{0: list<class-string>, 1: list<class-string>}
     */
    public function extract(array|string $paths, array $excludedPaths): array
    {
        $paths = is_string($paths) ? [$paths] : $paths;

        /** @var list<class-string> $includedClasses */
        $includedClasses = [];
        /** @var list<class-string> $excludedClasses */
        $excludedClasses = [];

        foreach ($paths as $path) {
            $extractedClasses = $this->extractFromPath($path);

            foreach ($extractedClasses as $classPath => $class) {
                $isExcluded = false;
                foreach ($excludedPaths as $excludedPath) {
                    if (str_starts_with($classPath, $excludedPath)) {
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
        $contents = file_get_contents($path);
        $tokens = token_get_all($contents);

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
        /** @var array{0: string} $file */
        foreach ($regexIterator as $file) {
            $path = $file[0];

            $className = $this->extractFromFile($path);
            if ($className !== null) {
                $classes[$path] = $className;
            }
        }

        return $classes;
    }
}

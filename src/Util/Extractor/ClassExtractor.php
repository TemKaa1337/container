<?php

declare(strict_types=1);

namespace Temkaa\Container\Util\Extractor;

use DirectoryIterator;

/**
 * @internal
 */
final class ClassExtractor
{
    private const string PHP_FILE_EXTENSION = 'php';

    /**
     * @param string[]|string $paths
     *
     * @return list<class-string>
     */
    public function extract(array|string $paths): array
    {
        $paths = is_string($paths) ? [$paths] : $paths;
        /** @var list<list<class-string>> $classes */
        $classes = [];

        foreach ($paths as $path) {
            $classes[] = $this->extractFromPath($path);
        }

        return array_merge(...$classes);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     *
     * @return list<class-string>
     */
    private function extractFromFile(string $path): array
    {
        $contents = file_get_contents($path);
        $tokens = token_get_all($contents);

        $nsTokens = [T_STRING => true, T_NS_SEPARATOR => true, T_NAME_QUALIFIED => true];

        $classes = [];

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
                        if (isset($nsTokens[$tokens[$i][0]])) {
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

                        if (!in_array($tokens[$j][0], [T_WHITESPACE, T_DOC_COMMENT, T_COMMENT], true)) {
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

                    $classes[] = ltrim($namespace.$class, '\\');
                    break;
                default:
                    break;
            }
        }

        return $classes;
    }

    /**
     * @return list<class-string>
     */
    private function extractFromPath(string $path): array
    {
        if (is_file($path)) {
            if (pathinfo($path, PATHINFO_EXTENSION) === self::PHP_FILE_EXTENSION) {
                return $this->extractFromFile($path);
            }

            return [];
        }

        /** @var list<list<class-string>> $classes */
        $classes = [];
        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot()) {
                continue;
            }

            $classes[] = $this->extractFromPath($file->getRealPath());
        }

        return array_merge(...$classes);
    }
}

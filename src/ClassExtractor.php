<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer;

use DirectoryIterator;
use Temkaa\SimpleContainer\Attribute\NonAutowirable;

#[NonAutowirable]
final class ClassExtractor
{
    /**
     * @return class-string[]
     */
    public function extract(string $path): array
    {
        $classes = [];
        if (is_file($path)) {
            if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                return $this->extractFromFile($path);
            }

            return [];
        }

        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot()) {
                continue;
            }

            $filePath = $file->getRealPath();
            if ($file->isFile() && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
                $classes = [...$classes, ...$this->extractFromFile($filePath)];
            } else if ($file->isDir()) {
                $classes = [...$classes, ...$this->extract($filePath)];
            }
        }

        return $classes;
    }

    /**
     * @return class-string[]
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
                    $isClassConstant = false;
                    for ($j = $i - 1; $j > 0; --$j) {
                        if (!isset($tokens[$j][1])) {
                            break;
                        }

                        if ($tokens[$j][0] === T_DOUBLE_COLON) {
                            $isClassConstant = true;
                            break;
                        }

                        if (!in_array($tokens[$j][0], [T_WHITESPACE, T_DOC_COMMENT, T_COMMENT], true)) {
                            break;
                        }
                    }

                    if ($isClassConstant) {
                        break;
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
}

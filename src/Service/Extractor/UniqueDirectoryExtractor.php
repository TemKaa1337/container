<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Extractor;

use function array_combine;
use function array_keys;
use function array_map;
use function array_unique;
use function array_values;
use function dirname;
use function rtrim;
use function str_ends_with;
use function str_replace;
use const DIRECTORY_SEPARATOR;

/**
 * @internal
 */
final readonly class UniqueDirectoryExtractor
{
    /**
     * Paths array must be an array of realpath'ed paths
     *
     * @param list<string> $paths
     *
     * @return list<string>
     */
    public function extract(array $paths): array
    {
        $paths = array_values(array_unique($paths));

        $pathMapping = array_combine(
            array_map(static fn (string $directory): string => rtrim($directory, characters: '\\/'), $paths),
            array_map(
                static fn (string $directory): string => str_ends_with($directory, '.php')
                    ? $directory
                    : str_replace(
                        ['\\', '/'],
                        DIRECTORY_SEPARATOR,
                        rtrim($directory, characters: '\\/').DIRECTORY_SEPARATOR,
                    ),
                $paths,
            ),
        );

        foreach (array_keys($pathMapping) as $dirname) {
            $latestDirname = $dirname;
            while (true) {
                $currentDirname = dirname($latestDirname);
                if (isset($pathMapping[$currentDirname])) {
                    unset($pathMapping[$dirname]);
                    break;
                }

                if ($latestDirname === $currentDirname) {
                    break;
                }

                $latestDirname = $currentDirname;
            }
        }

        return array_values($pathMapping);
    }
}

<?php

declare(strict_types=1);

namespace Temkaa\Container\Util\Extractor;

use function array_combine;
use function array_map;
use function array_unique;
use function array_values;
use function dirname;
use function rtrim;
use function sprintf;
use function str_ends_with;
use function str_replace;

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

        $topLevelDirectories = array_combine(
            array_map(static fn (string $directory): string => rtrim($directory, characters: '\\/'), $paths),
            array_map(
                static fn (string $directory): string => str_ends_with($directory, '.php')
                    ? $directory
                    : sprintf(
                        '%s/%s',
                        rtrim(
                            $directory,
                            characters: '\\/',
                        ),
                        '/'
                    ),
                $paths,
            ),
        );

        do {
            $iterate = false;
            foreach ($topLevelDirectories as $dirname => $realDirectories) {
                $latestDirname = $dirname;
                while (true) {
                    $currentDirname = dirname($latestDirname);
                    if (isset($topLevelDirectories[$currentDirname])) {
                        unset($topLevelDirectories[$dirname]);

                        $iterate = true;

                        break;
                    }

                    if ($latestDirname === $currentDirname) {
                        break;
                    }

                    $latestDirname = $currentDirname;
                }
            }
        } while ($iterate);

        return array_map(
            static fn (string $directory): string => str_replace('\\', '/', $directory),
            array_values($topLevelDirectories),
        );
    }
}

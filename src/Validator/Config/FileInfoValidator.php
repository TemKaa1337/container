<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use SplFileInfo;
use Symfony\Component\Filesystem\Exception\IOException;
use Temkaa\SimpleContainer\Exception\Config\EntryNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;

/**
 * @internal
 */
final class FileInfoValidator
{
    public function validate(SplFileInfo $file): void
    {
        if (!$file->getRealPath()) {
            throw new EntryNotFoundException(
                sprintf(
                    'Could not find container config in path "%s".',
                    $file->getPathname(),
                ),
            );
        }

        if (!$file->isReadable()) {
            throw new IOException(
                sprintf(
                    'Could not read contents of file "%s/%s".',
                    $file->getPath(),
                    $file->getFilename(),
                ),
            );
        }

        if ($file->getExtension() !== 'yaml') {
            throw new InvalidConfigNodeTypeException('Config file must have .yaml extension.');
        }
    }
}

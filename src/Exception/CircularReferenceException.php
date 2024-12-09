<?php

declare(strict_types=1);

namespace Temkaa\Container\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;
use function array_key_last;
use function array_map;
use function explode;
use function implode;
use function sprintf;

/**
 * @api
 */
final class CircularReferenceException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $id, array $references)
    {
        $formattedReferences = array_map(
            static function (string $referenceId): string {
                $parts = explode('\\', $referenceId);

                return $parts[array_key_last($parts)];
            },
            $references,
        );

        $entryClassParts = explode('\\', $id);
        $entryClassName = $entryClassParts[array_key_last($entryClassParts)];

        $message = sprintf(
            'Cannot instantiate class "%s" as it has circular references "%s".',
            $entryClassName,
            implode(' -> ', $formattedReferences),
        );

        parent::__construct($message);
    }
}

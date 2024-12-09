<?php

declare(strict_types=1);

namespace Temkaa\Container\Model\Reference\Deferred;

use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;
use Temkaa\Container\Model\Reference\ReferenceInterface;

/**
 * @internal
 */
final readonly class InstanceOfIteratorReference implements ReferenceInterface
{
    /**
     * @param class-string                $id
     * @param class-string[]              $exclude
     * @param array<class-string, string> $customFormatMapping
     */
    public function __construct(
        private string $id,
        private array $exclude,
        private IteratorFormat $format,
        private array $customFormatMapping,
    ) {
    }

    /**
     * @return array<class-string, string>
     */
    public function getCustomFormatMapping(): array
    {
        return $this->customFormatMapping;
    }

    /**
     * @return class-string[]
     */
    public function getExclude(): array
    {
        return $this->exclude;
    }

    public function getFormat(): IteratorFormat
    {
        return $this->format;
    }

    /**
     * @return class-string
     */
    public function getId(): string
    {
        return $this->id;
    }
}

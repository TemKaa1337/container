<?php

declare(strict_types=1);

namespace Temkaa\Container\Model\Reference\Deferred;

use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;
use Temkaa\Container\Model\Reference\ReferenceInterface;

/**
 * @internal
 */
final readonly class TaggedIteratorReference implements ReferenceInterface
{
    /**
     * @param string                      $tag
     * @param class-string[]              $exclude
     * @param IteratorFormat              $format
     * @param array<class-string, string> $customFormatMapping
     */
    public function __construct(
        private string $tag,
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

    public function getTag(): string
    {
        return $this->tag;
    }
}

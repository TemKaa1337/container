<?php

declare(strict_types=1);

namespace Temkaa\Container\Attribute\Bind;

use Attribute;
use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;

/**
 * @api
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class InstanceOfIterator extends AbstractIterator
{
    /**
     * When using $customFormatMapping, the key should be a target class name, e.g. TestClass::class,
     * and value must be your key, e.g. [TestClass::class => 'result_key_inside_class_array']
     *
     * @param class-string                $id
     * @param class-string[]              $exclude
     * @param array<class-string, string> $customFormatMapping
     */
    public function __construct(
        public string $id,
        public array $exclude = [],
        public IteratorFormat $format = IteratorFormat::List,
        public array $customFormatMapping = [],
    ) {
        $this->validate($this->customFormatMapping);
    }
}

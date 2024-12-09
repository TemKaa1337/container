<?php

declare(strict_types=1);

namespace Temkaa\Container\Enum\Attribute\Bind;

/**
 * @api
 */
enum IteratorFormat
{
    case ArrayWithClassNameKey;
    case ArrayWithClassNamespaceKey;
    case ArrayWithCustomKey;
    case List;
}

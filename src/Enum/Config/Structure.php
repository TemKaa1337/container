<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Enum\Config;

/**
 * @internal
 */
enum Structure: string
{
    case Bind = 'bind';
    case Decorates = 'decorates';
    case Exclude = 'exclude';
    case File = 'file';
    case Id = 'id';
    case Include = 'include';
    case Priority = 'priority';
    case Services = 'services';
    case Signature = 'signature';
    case Singleton = 'singleton';
    case Tags = 'tags';
}

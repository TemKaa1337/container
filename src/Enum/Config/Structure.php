<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Enum\Config;

enum Structure: string
{
    case Bind = 'bind';
    case Exclude = 'exclude';
    case File = 'file';
    case Include = 'include';
    case Services = 'services';
    case Singleton = 'singleton';
    case Tags = 'tags';
}

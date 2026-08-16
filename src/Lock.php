<?php

declare(strict_types=1);

namespace Aedon\VFS;

enum Lock: int
{
    case Exclusive = 1;
    case Shared = 2;
}

<?php

declare(strict_types=1);

namespace Aedon\VFS;

final class SymlinkNode extends Node
{
    public string $target;
    public Node|null $linkTarget = null;

    public function __construct(string $filename, string $path, int $permissions, int $userId, int $groupId, string $target, Node|null $linkTarget = null)
    {
        parent::__construct($filename, $path, $permissions, $userId, $groupId);

        $this->target = $target;
        $this->linkTarget = $linkTarget;
    }
}

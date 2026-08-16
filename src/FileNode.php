<?php

declare(strict_types=1);

namespace Aedon\VFS;

use function strlen;

final class FileNode extends Node
{
    public string $content;

    public function __construct(string $filename, string $path, int $permissions, int $userId, int $groupId, string $content)
    {
        parent::__construct($filename, $path, $permissions, $userId, $groupId);

        $this->content = $content;
        $this->size = strlen($content);
    }
}

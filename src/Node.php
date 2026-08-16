<?php

declare(strict_types=1);

namespace Aedon\VFS;

use function time;

abstract class Node
{
    public int $ctime;
    public int $mtime;
    public int $atime;
    public int $size = 0;

    public function __construct(
        public readonly string $filename,
        public readonly string $path,
        public int $permissions,
        public int $userId,
        public int $groupId
    ) {
        $time = time();

        $this->ctime = $time;
        $this->mtime = $time;
        $this->atime = $time;
    }

    /**
     * Get the path of the node in the VFS
     */
    public function path(): string
    {
        return VirtualFileSystem::PROTOCOL_PATH . '/' . $this->path;
    }
}

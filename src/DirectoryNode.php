<?php

declare(strict_types=1);

namespace Aedon\VFS;

final class DirectoryNode extends Node
{
    /**
     * @var array<string, Node>
     */
    public array $children;

    /**
     * @param array<string, Node> $children
     */
    public function __construct(string $filename, string $path, int $permissions, int $userId, int $groupId, array $children)
    {
        parent::__construct($filename, $path, $permissions, $userId, $groupId);

        $this->children = $children;
    }

    /**
     * @internal
     */
    public function addChild(Node $node): void
    {
        $this->children[$node->path] = $node;
    }

    /**
     * @internal
     */
    public function removeChild(string $path): void
    {
        unset($this->children[$path]);
    }
}

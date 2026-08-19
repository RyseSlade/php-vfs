<?php

declare(strict_types=1);

namespace Aedon\VFS;

final class DirectoryNode extends Node
{
    /**
     * @var array<array-key, Node>
     */
    public array $children;

    /**
     * @param array<array-key, Node> $children
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
        $this->children[] = $node;
    }

    /**
     * @internal
     */
    public function removeChild(string $path): void
    {
        foreach ($this->children as $index => $child) {
            if ($child->path === $path) {
                unset($this->children[$index]);
                break;
            }
        }
    }
}

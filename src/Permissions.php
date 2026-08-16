<?php

declare(strict_types=1);

namespace Aedon\VFS;

use function getmygid;
use function getmyuid;

final readonly class Permissions
{
    public function canRead(Node $node): bool
    {
        if ($this->isOwner($node)) {
            $permissions = ($node->permissions >> 6) & 7;

            if ($permissions & 4 && (!$node instanceof DirectoryNode || $permissions & 1)) {
                return true;
            }
        }

        if ($this->isInGroup($node)) {
            $permissions = ($node->permissions >> 3) & 7;

            if ($permissions & 4 && (!$node instanceof DirectoryNode || $permissions & 1)) {
                return true;
            }
        }

        $permissions = $node->permissions & 7;

        if ($permissions & 4 && (!$node instanceof DirectoryNode || $permissions & 1)) {
            return true;
        }

        return false;
    }

    public function canWrite(Node $node): bool
    {
        if ($this->isOwner($node)) {
            $permissions = ($node->permissions >> 6) & 7;

            if ($permissions & 2 && (!$node instanceof DirectoryNode || $permissions & 1)) {
                return true;
            }
        }

        if ($this->isInGroup($node)) {
            $permissions = ($node->permissions >> 3) & 7;

            if ($permissions & 2 && (!$node instanceof DirectoryNode || $permissions & 1)) {
                return true;
            }
        }

        $permissions = $node->permissions & 7;

        if ($permissions & 2 && (!$node instanceof DirectoryNode || $permissions & 1)) {
            return true;
        }

        return false;
    }

    private function isOwner(Node $node): bool
    {
        return getmyuid() === $node->userId;
    }

    private function isInGroup(Node $node): bool
    {
        return getmygid() === $node->groupId;
    }

    public function getMode(Node $node): int
    {
        if ($node instanceof FileNode) {
            return 0100000 | $node->permissions;
        } else if ($node instanceof DirectoryNode) {
            return 0040000 | $node->permissions;
        } else if ($node instanceof SymlinkNode) {
            return 0120000 | $node->permissions;
        }

        return 0;
    }
}

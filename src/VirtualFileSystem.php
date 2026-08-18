<?php

declare(strict_types=1);

namespace Aedon\VFS;

use RuntimeException;

use function array_keys;
use function assert;
use function basename;
use function in_array;
use function is_array;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function stream_get_wrappers;
use function stream_wrapper_register;
use function strrpos;
use function substr;

final class VirtualFileSystem
{
    public const string PROTOCOL = 'vfs';
    public const string PROTOCOL_PATH = 'vfs://root';
    public const int DEFAULT_PERMISSIONS = 0755;
    public const int DEFAULT_SYMLINK_PERMISSIONS = 0777;
    public const int DEFAULT_USER_ID = 1000;
    public const int DEFAULT_GROUP_ID = 1000;

    static private VirtualFileSystem|null $vfs = null;
    static private int $defaultPermissions = self::DEFAULT_PERMISSIONS;

    /** @var array<string, Node> */
    private array $nodes = [];

    /**
     * @internal
     */
    private function __construct(array $defaultStructure = [], int $defaultPermissions = self::DEFAULT_PERMISSIONS)
    {
        self::$defaultPermissions = $defaultPermissions;
        $this->recursiveCreateNodes('', $defaultStructure, $defaultPermissions);
        $this->rewriteSymlinks();
    }

    private function recursiveCreateNodes(string $parentKey, array $structure, int $defaultPermissions): void
    {
        /**
         * @var string $key
         * @var array|string $value
         */
        foreach ($structure as $key => $value) {
            $currentKey = $parentKey ? $parentKey . '/' . $key : $key;

            if (is_array($value)) {
                $this->recursiveCreateNodes($currentKey, $value, $defaultPermissions);

                $children = [];

                reset($this->nodes);

                foreach ($this->nodes as $node) {
                    if (!str_starts_with($node->path, $currentKey)) {
                        continue;
                    }

                    $remainingPath = substr($node->path, strlen($currentKey) + 1);

                    if (str_contains($remainingPath, '/')) {
                        continue;
                    }

                    $children[$node->path] = $node;
                }

                $this->nodes[$currentKey] = new DirectoryNode(
                    $key,
                    $currentKey,
                    $defaultPermissions,
                    self::DEFAULT_USER_ID,
                    self::DEFAULT_GROUP_ID,
                    $children
                );
            } else {
                if (str_starts_with($value, '@')) {
                    $this->nodes[$currentKey] = new SymlinkNode(
                        $key,
                        $currentKey,
                        $defaultPermissions === self::DEFAULT_PERMISSIONS ? self::DEFAULT_SYMLINK_PERMISSIONS : $defaultPermissions,
                        self::DEFAULT_USER_ID,
                        self::DEFAULT_GROUP_ID,
                        $value
                    );
                } else {
                    $this->nodes[$currentKey] = new FileNode(
                        $key,
                        $currentKey,
                        $defaultPermissions,
                        self::DEFAULT_USER_ID,
                        self::DEFAULT_GROUP_ID,
                        $value
                    );
                }
            }
        }
    }

    private function rewriteSymlinks(): void
    {
        foreach ($this->nodes as $node) {
            if ($node instanceof SymlinkNode) {
                $target = substr($node->target, 1);

                if (!isset($this->nodes[$target])) {
                    throw new RuntimeException('Symlink has invalid target "' . $target . '" or file content starts with "@"');
                }

                $node->linkTarget = $this->nodes[$target];
            }
        }
    }

    static public function create(array $structure = [], int $defaultPermissions = self::DEFAULT_PERMISSIONS): VirtualFileSystem
    {
        if (!in_array(self::PROTOCOL, stream_get_wrappers())) {
            if (!stream_wrapper_register(VirtualFileSystem::PROTOCOL, StreamWrapper::class)) {
                throw new RuntimeException('Could not register stream wrapper for virtual file system (' . self::PROTOCOL. ')');
            }
        }

        self::$vfs = new self($structure, $defaultPermissions);

        StreamWrapper::reset();

        return self::$vfs;
    }

    /**
     * @internal
     */
    static public function get(): VirtualFileSystem|null
    {
        return self::$vfs;
    }

    /**
     * Get the VFS path
     */
    public function path(string $path): string
    {
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        return self::PROTOCOL_PATH . $path;
    }

    /**
     * Get the node by its path for file manipulation
     */
    public function node(string $path): Node
    {
        // Handle path with protocol
        if (str_starts_with($path, self::PROTOCOL_PATH)) {
            $path = str_replace(self::PROTOCOL_PATH . '/', '', $path);
        }

        $node = $this->nodes[$path] ?? null;

        if ($node !== null) {
            return $node;
        }

        if (!str_contains($path, '/')) {
            $filename = $path;
        } else {
            $filename = substr($path, (int)strrpos($path, '/') + 1);
        }

        return new EmptyNode($filename, $path, self::$defaultPermissions, self::DEFAULT_USER_ID, self::DEFAULT_GROUP_ID);
    }

    /**
     * @internal
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    private function getFilenameFromPath(string $path): string
    {
        if (!str_contains($path, '/')) {
            $filename = $path;
        } else {
            $filename = substr($path, (int)strrpos($path, '/') + 1);
        }

        return $filename;
    }

    public function addFile(string $path, string $content): void
    {
        $filename = $this->getFilenameFromPath($path);

        if (str_contains($path, '/')) {
            $parentPath = substr($path, 0, (int)strrpos($path, '/'));

            $parentNode = $this->node($parentPath);

            if (!$parentNode instanceof DirectoryNode) {
                throw new RuntimeException('Can only add file to a directory node');
            }
        }

        $fileNode = new FileNode(
            $filename,
            $path,
            self::$defaultPermissions,
            self::DEFAULT_USER_ID,
            self::DEFAULT_GROUP_ID,
            $content
        );

        $this->nodes[$path] = $fileNode;

        if (isset($parentNode)) {
            assert($parentNode instanceof DirectoryNode);
            $parentNode->addChild($fileNode);
        }
    }

    public function addDirectory(string $path, array $structure = []): void
    {
        $filename = $this->getFilenameFromPath($path);

        if (str_contains($path, '/')) {
            $parentPath = substr($path, 0, (int)strrpos($path, '/'));

            $parentNode = $this->node($parentPath);

            if (!$parentNode instanceof DirectoryNode) {
                throw new RuntimeException('Can only add file to a directory node');
            }
        }

        $this->recursiveCreateNodes($path, $structure, self::$defaultPermissions);

        $children = [];

        foreach (array_keys($structure) as $childPath) {
            /** @var string $childPath */
            $children[$childPath] = $this->node($childPath);
        }

        $directoryNode = new DirectoryNode(
            $filename,
            $path,
            self::$defaultPermissions,
            self::DEFAULT_USER_ID,
            self::DEFAULT_GROUP_ID,
            $children
        );

        $this->nodes[$path] = $directoryNode;

        if (isset($parentNode)) {
            assert($parentNode instanceof DirectoryNode);
            $parentNode->addChild($directoryNode);
        }
    }

    public function addSymlink(string $path, string $target): void
    {
        $targetNode = $this->node($target);

        if ($targetNode instanceof EmptyNode) {
            throw new RuntimeException('Invalid link target');
        }

        $filename = $this->getFilenameFromPath($path);

        if (str_contains($path, '/')) {
            $parentPath = substr($path, 0, (int)strrpos($path, '/'));

            $parentNode = $this->node($parentPath);

            if (!$parentNode instanceof DirectoryNode) {
                throw new RuntimeException('Can only add file to a directory node');
            }
        }

        $symlinkNode = new SymlinkNode(
            $filename,
            $path,
            self::$defaultPermissions,
            self::DEFAULT_USER_ID,
            self::DEFAULT_GROUP_ID,
            $target
        );

        $this->nodes[$path] = $symlinkNode;

        if (isset($parentNode)) {
            assert($parentNode instanceof DirectoryNode);
            $parentNode->addChild($symlinkNode);
        }
    }

    public function removeNode(string $path): void
    {
        if (str_contains($path, '/')) {
            $parentPath = substr($path, 0, (int)strrpos($path, '/'));

            $parentNode = $this->node($parentPath);

            if (!$parentNode instanceof DirectoryNode) {
                throw new RuntimeException('Parent node must be a directory node');
            }

            $parentNode->removeChild($path);
        }

        $node = $this->node($path);

        if ($node instanceof DirectoryNode) {
            foreach ($node->children as $child) {
                $this->removeNode($child->path);
            }
        }

        unset($this->nodes[$path]);
    }

    public function directory(string $path): DirectoryNode|null
    {
        if (!str_contains($path, '/')) {
            return null;
        }

        $parentPath = substr($path, 0, (int)strrpos($path, '/'));

        $parentNode = $this->node($parentPath);

        if (!$parentNode instanceof DirectoryNode) {
            return null;
        }

        return $parentNode;
    }

    public function moveDirectory(string $fromPath, string $toPath): void
    {
        $fromNode = $this->node($fromPath);

        $fromNode->filename = basename($toPath);
        $fromNode->path = $toPath;

        $this->nodes[$toPath] = $fromNode;
        unset($this->nodes[$fromPath]);
    }
}

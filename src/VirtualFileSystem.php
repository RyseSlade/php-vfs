<?php

declare(strict_types=1);

namespace Aedon\VFS;

use RuntimeException;

use function array_keys;
use function dirname;
use function in_array;
use function is_array;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function stream_get_wrappers;
use function stream_wrapper_register;
use function strlen;
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

    /** @var array<array-key, Node> */
    private array $nodes = [];

    private DirectoryNode $root;

    /**
     * @internal
     */
    private function __construct(array $defaultStructure = [], int $defaultPermissions = self::DEFAULT_PERMISSIONS)
    {
        self::$defaultPermissions = $defaultPermissions;

        $this->recursiveCreateNodes('', $defaultStructure, $defaultPermissions);

        $rootChildren = [];

        foreach ($this->nodes as $node) {
            if (str_contains($node->path, '/')) {
                continue;
            }

            $rootChildren[] = $node;
        }

        $this->root = new DirectoryNode('/', '/', $defaultPermissions, self::DEFAULT_USER_ID, self::DEFAULT_GROUP_ID, $rootChildren);

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

                    $children[] = $node;
                }

                $this->nodes[] = new DirectoryNode(
                    $key,
                    $currentKey,
                    $defaultPermissions,
                    self::DEFAULT_USER_ID,
                    self::DEFAULT_GROUP_ID,
                    $children
                );
            } else {
                if (str_starts_with($value, '@')) {
                    $this->nodes[] = new SymlinkNode(
                        $key,
                        $currentKey,
                        $defaultPermissions === self::DEFAULT_PERMISSIONS ? self::DEFAULT_SYMLINK_PERMISSIONS : $defaultPermissions,
                        self::DEFAULT_USER_ID,
                        self::DEFAULT_GROUP_ID,
                        $value
                    );
                } else {
                    $this->nodes[] = new FileNode(
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

                $targetNode = $this->node($target);

                if ($targetNode instanceof EmptyNode) {
                    throw new RuntimeException('Symlink has invalid target "' . $target . '" or file content starts with "@"');
                }

                $node->linkTarget = $targetNode;
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
        if ($path === '/' || $path === $this->path('/')) {
            return $this->root;
        }

        // Handle path with protocol
        if (str_starts_with($path, self::PROTOCOL_PATH)) {
            $path = str_replace(self::PROTOCOL_PATH . '/', '', $path);
        }

        $currentNode = null;

        foreach ($this->nodes as $node) {
            if ($node->path === $path) {
                $currentNode = $node;
                break;
            }
        }

        if ($currentNode !== null) {
            return $currentNode;
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

    private function getNodeIndexByPath(string $path): int|null
    {
        foreach ($this->nodes as $index => $node) {
            if ($node->path === $path) {
                return (int)$index;
            }
        }

        return null;
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
        if (!($this->node($path) instanceof EmptyNode)) {
            throw new RuntimeException('File already exists');
        }

        $filename = $this->getFilenameFromPath($path);

        $fileNode = new FileNode(
            $filename,
            $path,
            self::$defaultPermissions,
            self::DEFAULT_USER_ID,
            self::DEFAULT_GROUP_ID,
            $content
        );

        $this->nodes[] = $fileNode;

        $parentNode = $this->directory($path);

        if ($parentNode instanceof DirectoryNode) {
            $parentNode->addChild($fileNode);
        }
    }

    public function addDirectory(string $path, array $structure = []): void
    {
        if (!($this->node($path) instanceof EmptyNode)) {
            throw new RuntimeException('Directory already exists');
        }

        $filename = $this->getFilenameFromPath($path);

        $this->recursiveCreateNodes($path, $structure, self::$defaultPermissions);

        $children = [];

        foreach (array_keys($structure) as $childPath) {
            /** @var string $childPath */
            $children[] = $this->node($childPath);
        }

        $directoryNode = new DirectoryNode(
            $filename,
            $path,
            self::$defaultPermissions,
            self::DEFAULT_USER_ID,
            self::DEFAULT_GROUP_ID,
            $children
        );

        $this->nodes[] = $directoryNode;

        $parentNode = $this->directory($path);

        if ($parentNode instanceof DirectoryNode) {
            $parentNode->addChild($directoryNode);
        }
    }

    public function addSymlink(string $path, string $target): void
    {
        if (!($this->node($path) instanceof EmptyNode)) {
            throw new RuntimeException('Symlink already exists');
        }

        $targetNode = $this->node($target);

        if ($targetNode instanceof EmptyNode) {
            throw new RuntimeException('Invalid link target');
        }

        $filename = $this->getFilenameFromPath($path);

        $symlinkNode = new SymlinkNode(
            $filename,
            $path,
            self::$defaultPermissions,
            self::DEFAULT_USER_ID,
            self::DEFAULT_GROUP_ID,
            $target
        );

        $this->nodes[] = $symlinkNode;

        $parentNode = $this->directory($path);

        if ($parentNode instanceof DirectoryNode) {
            $parentNode->addChild($symlinkNode);
        }
    }

    /**
     * @internal
     */
    public function removeNode(string $path): void
    {
        if ($path === '/') {
            throw new RuntimeException('Cannot remove root');
        }

        $node = $this->node($path);

        if ($node instanceof EmptyNode) {
            throw new RuntimeException('Node does not exist');
        }

        $parentNode = $this->directory($path);

        if ($parentNode instanceof DirectoryNode) {
            $parentNode->removeChild($path);
        }

        if ($node instanceof DirectoryNode) {
            foreach ($node->children as $child) {
                $this->removeNode($child->path);
            }
        }

        if (($index = $this->getNodeIndexByPath($path)) !== null) {
            unset($this->nodes[$index]);
        }
    }

    /**
     * @internal
     */
    public function directory(string $path): DirectoryNode|null
    {
        if (!str_contains($path, '/')) {
            return $this->root;
        }

        $parentPath = substr($path, 0, (int)strrpos($path, '/'));

        $parentNode = $this->node($parentPath);

        if (!$parentNode instanceof DirectoryNode) {
            return null;
        }

        return $parentNode;
    }

    /**
     * @internal
     */
    public function renameNode(string $fromPath, string $toPath): void
    {
        if ($fromPath === '/') {
            throw new RuntimeException('Cannot rename root');
        }

        if (dirname($fromPath) !== dirname($toPath)) {
            throw new RuntimeException('Cannot rename nodes in different directories');
        }

        if ($this->node($fromPath) instanceof EmptyNode) {
            throw new RuntimeException('Node does not exist');
        }

        if (!($this->node($toPath) instanceof EmptyNode)) {
            throw new RuntimeException('Node already exists');
        }

        $fromNode = $this->node($fromPath);
        $toNode = $this->node($toPath);

        foreach ($this->nodes as $node) {
            if ($node instanceof SymlinkNode && $node->target === '@' . $fromNode->path) {
                $node->target = '@' . $toNode->path;
            }
        }

        $fromNode->filename = $toNode->filename;
        $fromNode->path = $toNode->path;
    }
}

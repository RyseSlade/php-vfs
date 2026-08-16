<?php

declare(strict_types=1);

namespace Aedon\VFS;

use RuntimeException;
use UnexpectedValueException;
use ValueError;

use function assert;
use function call_user_func_array;
use function count;
use function explode;
use function get_class;
use function getmyuid;
use function is_int;
use function method_exists;
use function str_contains;
use function str_pad;
use function strlen;
use function substr;
use function time;

/**
 * @internal
 */
final class StreamWrapper
{
    /** @psalm-suppress PossiblyUnusedProperty */
    public mixed $context = null;

    /** @var array<StreamWrapperContext> */
    static private array $streamWrapperContexts = [];

    static private Permissions|null $permissions = null;

    static private bool $linkStatRequested = false;

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }

        throw new RuntimeException('Stream function "' . $name . '" is not implemented');
    }

    static public function reset(): void
    {
        self::$streamWrapperContexts = [];
    }

    private function vfs(): VirtualFileSystem
    {
        $vfs = VirtualFileSystem::get();

        if ($vfs === null) {
            throw new RuntimeException('VFS is not initialized');
        }

        return $vfs;
    }

    private function permissions(): Permissions
    {
        if (self::$permissions === null) {
            self::$permissions = new Permissions();
        }

        return self::$permissions;
    }

    private function registerStreamWrapper(StreamWrapper $streamWrapper, Node $node, string $mode = 'r'): StreamWrapperContext
    {
        return new StreamWrapperContext($streamWrapper, $node, $mode);
    }

    private function streamWrapperContext(): StreamWrapperContext|null
    {
        foreach (self::$streamWrapperContexts as $streamWrapperContext) {
            if ($streamWrapperContext->streamWrapper === $this) {
                return $streamWrapperContext;
            }
        }

        return null;
    }

    private function isReadMode(string $mode): bool
    {
        if (
            str_contains($mode, 'r')
            || str_contains($mode, 'r+')
            || str_contains($mode, 'w+')
            || str_contains($mode, 'a+')
            || str_contains($mode, 'x+')
            || str_contains($mode, 'c+')
        ) {
            return true;
        }

        return false;
    }

    private function isWriteMode(string $mode): bool
    {
        if (
            str_contains($mode, 'a')
            || str_contains($mode, 'w')
            || str_contains($mode, 'c')
            || str_contains($mode, 'x')
            || str_contains($mode, 'r+')
        ) {
            return true;
        }

        return false;
    }

    private function stream_stat(): array|false
    {
        $streamWrapperContext = $this->streamWrapperContext();

        assert($streamWrapperContext !== null);

        $node = $streamWrapperContext->node;

        if ($node instanceof EmptyNode) {
            return false;
        }

        if (!self::$linkStatRequested && $node instanceof SymlinkNode && $node->linkTarget) {
            $node = $node->linkTarget;
        }

        $mode = $this->permissions()->getMode($node);

        $stat = [
            0 => 1,
            'dev' => 1,
            1 => 1,
            'ino' => 1,
            2 => 1,
            'mode' => $mode,
            3 => $mode,
            'nlink' => 1,
            4 => $node->userId,
            'uid' => $node->userId,
            5 => $node->groupId,
            'gid' => $node->groupId,
            6 => 1,
            'rdev' => 1,
            7 => $node->size,
            'size' => $node->size,
            8 => $node->atime,
            'atime' => $node->atime,
            9 => $node->mtime,
            'mtime' => $node->mtime,
            10 => $node->ctime,
            'ctime' => $node->ctime,
            11 => 4096,
            'blksize' => 4096,
            12 => 8,
            'blocks' => 8,
        ];

        return $stat;
    }

    private function url_stat(string $path, int $flags): array|false
    {
        $node = $this->vfs()->node($path);

        if ($this->streamWrapperContext() === null) {
            self::$streamWrapperContexts[] = $this->registerStreamWrapper($this, $node);
        }

        self::$linkStatRequested = (bool)($flags & STREAM_URL_STAT_LINK);

        if ($node instanceof EmptyNode) {
            return false;
        }

        return $this->stream_stat();
    }

    private function createFileNode(EmptyNode $node): FileNode|false
    {
        if (str_contains($node->path, '/')) {
            $directory = $this->vfs()->directory($node->path);

            if ($directory === null) {
                return false;
            }

            if (!$this->permissions()->canWrite($directory)) {
                return false;
            }
        } else if (!$this->permissions()->canWrite($node)) {
            return false;
        }

        $this->vfs()->addFile($node->path, '');

        $node = $this->vfs()->node($node->path);

        assert($node instanceof FileNode);

        return $node;
    }

    private function stream_open(string $path, string $mode, int $options, string|null &$opened_path): bool
    {
        $node = $this->vfs()->node($path);

        if (str_contains($mode, 'x')) {
            if (!$node instanceof EmptyNode) {
                return false;
            }

            $node = $this->createFileNode($node);

            if ($node === false) {
                return false;
            }
        } else if ((str_contains($mode, 'a') || str_contains($mode, 'w') || str_contains($mode, 'c')) && $node instanceof EmptyNode) {
            $node = $this->createFileNode($node);

            if ($node === false) {
                return false;
            }
        }

        if ($node instanceof SymlinkNode && $node->linkTarget) {
            $node = $node->linkTarget;
        }

        if (!$node instanceof FileNode) {
            return false;
        }

        if ($this->isReadMode($mode) && !$this->permissions()->canRead($node)) {
            return false;
        }

        if ($this->isWriteMode($mode) && !$this->permissions()->canWrite($node)) {
            return false;
        }

        if ($this->streamWrapperContext() === null) {
            self::$streamWrapperContexts[] = $this->registerStreamWrapper($this, $node, $mode);
        }

        if (str_contains($mode, 'a')) {
            $streamWrapperContext = $this->streamWrapperContext();

            assert($streamWrapperContext !== null);

            $node = $streamWrapperContext->node;

            assert($node instanceof FileNode);

            $streamWrapperContext->position = strlen($node->content);
        } else if (str_contains($mode, 'w')) {
            $node->content = '';
        }

        if ($options & STREAM_USE_PATH) {
            $opened_path = $node->path();
        }

        return true;
    }

    private function stream_read(int $count): string|false
    {
        $streamWrapperContext = $this->streamWrapperContext();

        if ($streamWrapperContext === null) {
            return false;
        }

        $node = $streamWrapperContext->node;

        assert($node instanceof FileNode);

        $content = $node->content;

        // Can read all content right away
        if ($streamWrapperContext->position === 0 && strlen($content) <= $count) {
            $streamWrapperContext->position = strlen($content);

            return $content;
        } else if ($streamWrapperContext->position === 0) { // Read partial content from beginning
            $partialContent = substr($content, $count);

            $streamWrapperContext->position = strlen($partialContent);

            return $partialContent;
        }

        $remainingContent = substr($content, $streamWrapperContext->position);

        // Can read remaining content right away
        if (strlen($remainingContent) <= $count) {
            $streamWrapperContext->position = strlen($content);

            return $remainingContent;
        }

        $partialContent = substr($content, $streamWrapperContext->position, $count);

        $streamWrapperContext->position += strlen($partialContent);

        return $partialContent;
    }

    private function stream_seek(int $offset, int $whence): bool|int
    {
        $streamWrapperContext = $this->streamWrapperContext();

        assert($streamWrapperContext !== null);

        $node = $streamWrapperContext->node;

        assert($node instanceof FileNode);

        if ($whence === SEEK_SET) {
            if ($offset > strlen($node->content)) {
                return false;
            }

            $streamWrapperContext->position = $offset;
        } else if ($whence === SEEK_CUR) {
            if ($streamWrapperContext->position + $offset > strlen($node->content)) {
                return false;
            }

            $streamWrapperContext->position += $offset;
        } else if ($whence === SEEK_END) {
            if (strlen($node->content) + $offset > strlen($node->content)) {
                return false;
            }

            $streamWrapperContext->position = strlen($node->content) + $offset;
        } else {
            throw new UnexpectedValueException();
        }

        return true;
    }

    private function stream_tell(): int
    {
        $streamWrapperContext = $this->streamWrapperContext();

        assert($streamWrapperContext !== null);

        return $streamWrapperContext->position;
    }

    private function stream_eof(): bool
    {
        $streamWrapperContext = $this->streamWrapperContext();

        assert($streamWrapperContext !== null);

        $node = $streamWrapperContext->node;

        assert($node instanceof FileNode);

        return $streamWrapperContext->position >= strlen($node->content) - 1;
    }

    private function stream_close(): void
    {
        $streamWrapperContext = $this->streamWrapperContext();

        if ($streamWrapperContext === null) {
            return;
        }

        $streamWrapperContext->node->atime = time();

        foreach (self::$streamWrapperContexts as $index => $streamWrapperContext) {
            if ($streamWrapperContext->streamWrapper === $this) {
                unset(self::$streamWrapperContexts[$index]);
            }
        }
    }

    private function stream_metadata(string $path, int $option, mixed $value): bool
    {
        $node = $this->vfs()->node($path);

        if ($node instanceof EmptyNode || (!$this->permissions()->canRead($node) && getmyuid() !== $node->userId)) {
            return false;
        }

        switch ($option) {
            case STREAM_META_TOUCH:
                if ($node instanceof SymlinkNode && $node->linkTarget) {
                    $node = $node->linkTarget;
                }

                if (!$this->permissions()->canWrite($node)) {
                    return false;
                }
                $node->mtime = time();
                break;
            case STREAM_META_OWNER_NAME:
                throw new RuntimeException('STREAM_META_OWNER_NAME is not implemented');
            case STREAM_META_OWNER:
                if (!$this->permissions()->canWrite($node) || !is_int($value)) {
                    return false;
                }
                $node->userId = $value;
                break;
            case STREAM_META_GROUP_NAME:
                throw new RuntimeException('STREAM_META_GROUP_NAME is not implemented');
            case STREAM_META_GROUP:
                if (!$this->permissions()->canWrite($node) || !is_int($value)) {
                    return false;
                }
                $node->groupId = $value;
                break;
            case STREAM_META_ACCESS:
                if (!$this->permissions()->canWrite($node) && getmyuid() !== $node->userId) {
                    return false;
                }

                if (is_int($value)) {
                    $node->permissions = $value;
                } else {
                    throw new RuntimeException('STREAM_META_ACCESS is not implemented');
                }
                break;
            default:
                return false;
        }

        return true;
    }

    private function unlink(string $path): bool
    {
        $node = $this->vfs()->node($path);

        if (!$node instanceof FileNode && !$node instanceof SymlinkNode) {
            return false;
        }

        if (!$this->permissions()->canWrite($node)) {
            return false;
        }

        $this->vfs()->removeNode($node->path);

        return true;
    }

    private function rmdir(string $path, int $options): bool
    {
        $node = $this->vfs()->node($path);

        if (!$node instanceof DirectoryNode) {
            return false;
        }

        if (!$this->permissions()->canWrite($node)) {
            return false;
        }

        if (!($options & STREAM_MKDIR_RECURSIVE) && !empty($node->children)) {
            return false;
        }

        $this->vfs()->removeNode($node->path);

        return true;
    }

    private function stream_write(string $data): int
    {
        $streamWrapperContext = $this->streamWrapperContext();

        assert($streamWrapperContext !== null);

        $node = $streamWrapperContext->node;

        if (!$this->permissions()->canWrite($node)) {
            return 0;
        }

        if (!$node instanceof FileNode) {
            return 0;
        }

        $node->content .= $data;

        return strlen($data);
    }

    private function stream_flush(): bool
    {
        return true;
    }

    private function mkdir(string $path, int $mode, int $options): bool
    {
        $node = $this->vfs()->node($path);

        if (!$node instanceof EmptyNode) {
            return false;
        }

        if (!str_contains($node->path, '/')) {
            $this->vfs()->addDirectory($node->path);
            $this->vfs()->node($path)->permissions = $mode;
        } else {
            $recursive = (bool)($options & STREAM_MKDIR_RECURSIVE);

            $current = '';
            $parts = explode('/', $node->path);
            $partCount = count($parts);

            foreach ($parts as $index => $part) {
                $parentPath = $current ? $current . '/' . $part : $part;

                $parent = $this->vfs()->node($parentPath);

                if ($parent instanceof DirectoryNode) {
                    if (!$this->permissions()->canWrite($parent)) {
                        return false;
                    }
                } else if ($parent instanceof EmptyNode) {
                    if ($index < $partCount - 1 && !$recursive) {
                        return false;
                    }

                    $this->vfs()->addDirectory($parentPath);
                    $this->vfs()->node($parentPath)->permissions = $mode;
                } else if (!$parent instanceof DirectoryNode) {
                    throw new UnexpectedValueException('Cannot add to node type "' . get_class($parent) . '"');
                }

                $current = $parentPath;
            }
        }

        return true;
    }

    /**
     * @psalm-suppress UnusedParam
     */
    private function stream_set_option(int $option, int $arg1, int $arg2): bool
    {
        return false;
    }

    private function stream_truncate(int $new_size): bool
    {
        $streamWrapperContext = $this->streamWrapperContext();

        assert($streamWrapperContext !== null);

        $node = $streamWrapperContext->node;

        if (!$this->permissions()->canWrite($node)) {
            return false;
        }

        if (!$node instanceof FileNode) {
            return false;
        }

        if ($new_size < strlen($node->content)) {
            $node->content = substr($node->content, 0, $new_size);
        } else {
            $node->content = str_pad($node->content, $new_size, "\0", STR_PAD_RIGHT);
        }

        return true;
    }

    private function stream_lock(int $operation): bool
    {
        match ($operation) {
            LOCK_SH => 'LOCK_SH',
            LOCK_SH | LOCK_NB => 'LOCK_SH | LOCK_NB',
            LOCK_EX => 'LOCK_EX',
            LOCK_EX | LOCK_NB => 'LOCK_EX | LOCK_NB',
            LOCK_NB => 'LOCK_NB',
            LOCK_UN => 'LOCK_UN',
            FILE_APPEND | LOCK_EX => 'FILE_APPEND | LOCK_EX',
            0 => 'PHP_STREAM_LOCK_TEST',
            default => throw new ValueError('flock(): Argument #2 ($operation) must be one of LOCK_SH, LOCK_EX, or LOCK_UN'),
        };

        if ($operation === 0) {
            return true;
        }

        $streamWrapperContext = $this->streamWrapperContext();

        assert($streamWrapperContext !== null);

        $lock = false;

        foreach (self::$streamWrapperContexts as $otherStreamWrapperContext) {
            // Streams with same path
            if ($otherStreamWrapperContext->node->path === $streamWrapperContext->node->path) {
                if ($otherStreamWrapperContext->streamWrapper === $streamWrapperContext->streamWrapper) {
                    continue;
                }

                $lock = $otherStreamWrapperContext->lock;

                break;
            }
        }

        if (($operation & LOCK_UN) === LOCK_UN) {
            $streamWrapperContext->lock = false;

            return true;
        }

        if (($operation & LOCK_EX) === LOCK_EX && $lock === false) {
            $streamWrapperContext->lock = Lock::Exclusive;

            return true;
        } else if (($operation & LOCK_SH) === LOCK_SH && ($lock === false || (!$this->isWriteMode($streamWrapperContext->mode) && $lock === Lock::Shared))) {
            $streamWrapperContext->lock = Lock::Shared;

            return true;
        }

        if (($operation & LOCK_NB) !== LOCK_NB) {
            throw new RuntimeException('Blocking locks are not supported');
        }

        return false;
    }
}

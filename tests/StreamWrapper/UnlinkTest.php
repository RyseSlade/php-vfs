<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\DirectoryNode;
use Aedon\VFS\EmptyNode;
use Aedon\VFS\FileNode;
use Aedon\VFS\StreamWrapper;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

use function unlink;

#[CoversMethod(StreamWrapper::class, 'unlink')]
final class UnlinkTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldDeleteFile(): void
    {
        $subject = $this->buildSubject();

        $result = unlink($subject->path('filename'));

        self::assertTrue($result);
        self::assertInstanceOf(EmptyNode::class, $subject->node('filename'));
    }

    public function testShouldDeleteSymlink(): void
    {
        $subject = $this->buildSubject();

        $result = unlink($subject->path('symlink'));

        self::assertTrue($result);
        self::assertInstanceOf(EmptyNode::class, $subject->node('symlink'));
    }

    public function testShouldReturnFalseWhenDeletingNonExistantFile(): void
    {
        $subject = $this->buildSubject();

        $result = unlink($subject->path('z'));

        self::assertFalse($result);
        self::assertInstanceOf(EmptyNode::class, $subject->node('z'));
    }

    public function testShouldReturnFalseWhenDeletingFileWithoutWriteAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = unlink($subject->path('filename'));

        self::assertFalse($result);
        self::assertInstanceOf(FileNode::class, $subject->node('filename'));
    }

    public function testShouldReturnFalseWhenDeletingDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = unlink($subject->path('directory'));

        self::assertFalse($result);
        self::assertInstanceOf(DirectoryNode::class, $subject->node('directory'));
    }
}

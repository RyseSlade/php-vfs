<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\DirectoryNode;
use Aedon\VFS\EmptyNode;
use Aedon\VFS\FileNode;
use Aedon\VFS\StreamWrapper;
use Aedon\VFS\SymlinkNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

use function rmdir;

#[CoversMethod(StreamWrapper::class, 'rmdir')]
final class RmdirTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => '',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldDeleteDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = rmdir($subject->path('directory'));

        self::assertTrue($result);
        self::assertInstanceOf(EmptyNode::class, $subject->node('directory'));
    }

    public function testShouldReturnFalseWhenDeletingDirectoryWithoutWriteAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('directory')->permissions = 0000;

        $result = rmdir($subject->path('directory'));

        self::assertFalse($result);
        self::assertInstanceOf(DirectoryNode::class, $subject->node('directory'));
    }

    public function testShouldReturnFalseWhenDeletingNonEmptyDirectory(): void
    {
        $subject = $this->buildSubject();

        $subject->addFile('directory/newfile', '');

        $result = rmdir($subject->path('directory'));

        self::assertFalse($result);
        self::assertInstanceOf(DirectoryNode::class, $subject->node('directory'));
    }

    public function testShouldReturnFalseWhenDeletingFile(): void
    {
        $subject = $this->buildSubject();

        $result = rmdir($subject->path('filename'));

        self::assertFalse($result);
        self::assertInstanceOf(FileNode::class, $subject->node('filename'));
    }

    public function testShouldReturnFalseWhenDeletingSymlink(): void
    {
        $subject = $this->buildSubject();

        $result = rmdir($subject->path('symlink'));

        self::assertFalse($result);
        self::assertInstanceOf(SymlinkNode::class, $subject->node('symlink'));
        self::assertInstanceOf(FileNode::class, $subject->node('filename'));
    }
}

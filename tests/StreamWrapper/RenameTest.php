<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\DirectoryNode;
use Aedon\VFS\EmptyNode;
use Aedon\VFS\FileNode;
use Aedon\VFS\SymlinkNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function rename;

final class RenameTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [
                'file' => '',
            ],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldRenameFile(): void
    {
        $subject = $this->buildSubject();

        $result = rename($subject->path('filename'), $subject->path('filename2'));

        self::assertTrue($result);

        /** @var FileNode $node */
        $node = $subject->node('filename2');

        self::assertInstanceOf(FileNode::class, $node);
        self::assertEquals('content', $node->content);
        self::assertEquals(7, $node->size);
    }

    public function testShouldRenameDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = rename($subject->path('directory'), $subject->path('directory2'));

        self::assertTrue($result);

        /** @var DirectoryNode $node */
        $node = $subject->node('directory2');

        self::assertInstanceOf(DirectoryNode::class, $node);
        self::assertCount(1, $node->children);

        self::assertInstanceOf(EmptyNode::class, $subject->node('directory'));

        $nodes = $subject->getNodes();

        self::assertArrayNotHasKey('directory', $nodes);
    }

    public function testShouldRenameSymlink(): void
    {
        $subject = $this->buildSubject();

        $result = rename($subject->path('symlink'), $subject->path('symlink2'));

        self::assertTrue($result);

        /** @var SymlinkNode $node */
        $node = $subject->node('symlink2');

        self::assertInstanceOf(SymlinkNode::class, $node);
        self::assertEquals('filename', $node->target);
    }
}

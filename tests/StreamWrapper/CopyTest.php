<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\EmptyNode;
use Aedon\VFS\FileNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function copy;

final class CopyTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
        ]);
    }

    public function testShouldCopyFile(): void
    {
        $subject = $this->buildSubject();

        $result = copy($subject->path('filename'), $subject->path('directory/filename'));

        self::assertTrue($result);
        self::assertInstanceOf(FileNode::class, $subject->node('directory/filename'));

        /** @var FileNode $node */
        $node = $subject->node('directory/filename');

        self::assertEquals('content', $node->content);
    }

    public function testShouldReturnFalseWhenCopyingFileWithoutReadAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0333;

        $result = copy($subject->path('filename'), $subject->path('directory/filename'));

        self::assertFalse($result);
        self::assertInstanceOf(EmptyNode::class, $subject->node('directory/filename'));
    }

    public function testShouldReturnFalseWhenCopyingFileWithoutWriteAccessToTargetDirectory(): void
    {
        $subject = $this->buildSubject();

        $subject->node('directory')->permissions = 0555;

        $result = copy($subject->path('filename'), $subject->path('directory/filename'));

        self::assertFalse($result);
        self::assertInstanceOf(EmptyNode::class, $subject->node('directory/filename'));
    }
}

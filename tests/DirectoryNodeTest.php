<?php

declare(strict_types=1);

namespace Aedon\VFSTest;

use Aedon\VFS\DirectoryNode;
use Aedon\VFS\FileNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function time;

final class DirectoryNodeTest extends TestCase
{
    private function buildSubject(): DirectoryNode
    {
        return new DirectoryNode('directoryA', 'directoryA', VirtualFileSystem::DEFAULT_PERMISSIONS, 1000, 2000, [
            'directoryA/filename' => new FileNode('filename', 'directoryA/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, 1000, 2000, ''),
            'directoryA/directoryB' => new DirectoryNode('directoryB', 'directoryA/directoryB', VirtualFileSystem::DEFAULT_PERMISSIONS, 1000, 2000, []),
        ]);
    }

    public function testShouldGetPath(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals('directoryA', $subject->path);
        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH . '/directoryA', $subject->path());
    }

    public function testShouldGetFilename(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals('directoryA', $subject->filename);
    }

    public function testShouldGetPermissions(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals(VirtualFileSystem::DEFAULT_PERMISSIONS, $subject->permissions);
    }

    public function testShouldGetUserId(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals(1000, $subject->userId);
    }

    public function testShouldGetGroupId(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals(2000, $subject->groupId);
    }

    public function testShouldGetTimestamps(): void
    {
        $subject = $this->buildSubject();

        $fiveSecondsAgo = time() - 5;

        self::assertGreaterThanOrEqual($fiveSecondsAgo, $subject->ctime);
        self::assertGreaterThanOrEqual($fiveSecondsAgo, $subject->mtime);
        self::assertGreaterThanOrEqual($fiveSecondsAgo, $subject->atime);
    }

    public function testShouldGetSize(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals(0, $subject->size);
    }

    public function testShouldGetChildren(): void
    {
        $subject = $this->buildSubject();

        self::assertCount(2, $subject->children);

        self::assertInstanceOf(FileNode::class, $subject->children['directoryA/filename']);
        self::assertInstanceOf(DirectoryNode::class, $subject->children['directoryA/directoryB']);
    }

    public function testShouldAddChild(): void
    {
        $subject = $this->buildSubject();

        $subject->addChild(new FileNode('filename', 'directoryA/directoryB/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, ''));

        self::assertInstanceOf(FileNode::class, $subject->children['directoryA/directoryB/filename']);
    }

    public function testShouldRemoveChild(): void
    {
        $subject = $this->buildSubject();

        $subject->removeChild('directoryA/directoryB');

        self::assertFalse(isset($subject->children['directoryA/directoryB']));
    }
}

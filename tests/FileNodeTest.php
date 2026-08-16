<?php

declare(strict_types=1);

namespace Aedon\VFSTest;

use Aedon\VFS\FileNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

final class FileNodeTest extends TestCase
{
    private function buildSubject(): FileNode
    {
        return new FileNode('filename', 'directoryA/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, 1000, 2000, 'content');
    }

    public function testShouldGetPath(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals('directoryA/filename', $subject->path);
        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH . '/directoryA/filename', $subject->path());
    }

    public function testShouldGetFilename(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals('filename', $subject->filename);
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

        self::assertEquals(7, $subject->size);
    }

    public function testShouldGetContent(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals('content', $subject->content);
    }

    public function testShouldUpdateContent(): void
    {
        $subject = $this->buildSubject();

        $subject->content = 'new content';

        self::assertEquals('new content', $subject->content);
    }
}

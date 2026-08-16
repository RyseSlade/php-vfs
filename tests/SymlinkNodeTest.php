<?php

declare(strict_types=1);

namespace Aedon\VFSTest;

use Aedon\VFS\SymlinkNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function time;

final class SymlinkNodeTest extends TestCase
{
    private function buildSubject(): SymlinkNode
    {
        return new SymlinkNode('symlink', 'symlink', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, '@file');
    }

    public function testShouldGetPath(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals('symlink', $subject->path);
        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH . '/symlink', $subject->path());
    }

    public function testShouldGetFilename(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals('symlink', $subject->filename);
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

        self::assertEquals(1000, $subject->groupId);
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

    public function testShouldGetTarget(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals('@file', $subject->target);
    }

    public function testShouldGetLinkTarget(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals(null, $subject->linkTarget);
    }
}

<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function fileperms;

final class FilepermsTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetFilePerms(): void
    {
        $subject = $this->buildSubject();

        $result = fileperms($subject->path('filename'));

        self::assertEquals(33261, $result);
    }

    public function testShouldGetDirectoryPerms(): void
    {
        $subject = $this->buildSubject();

        $result = fileperms($subject->path('directory'));

        self::assertEquals(16877, $result);
    }

    public function testShouldGetSymlinkPerms(): void
    {
        $subject = $this->buildSubject();

        $result = fileperms($subject->path('symlink'));

        self::assertEquals(33261, $result);
    }

    public function testShouldGetFilePermsWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = fileperms($subject->path('filename'));

        self::assertEquals(32768, $result);
    }
}

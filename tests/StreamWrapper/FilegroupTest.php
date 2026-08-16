<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function filegroup;

final class FilegroupTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetFilegroupForFile(): void
    {
        $subject = $this->buildSubject();

        $result = filegroup($subject->path('filename'));

        self::assertEquals(1000, $result);
    }

    public function testShouldGetFilegroupForDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = filegroup($subject->path('directory'));

        self::assertEquals(1000, $result);
    }

    public function testShouldGetFilegroupForSymlinkActualFile(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->groupId = 2000;

        $result = filegroup($subject->path('symlink'));

        self::assertEquals(2000, $result);
    }

    public function testShouldGetFilegroupWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = filegroup($subject->path('filename'));

        self::assertEquals(1000, $result);
    }
}

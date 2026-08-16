<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function filesize;

final class FilesizeTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetFileSize(): void
    {
        $subject = $this->buildSubject();

        $result = filesize($subject->path('filename'));

        self::assertEquals(7, $result);
    }

    public function testShouldGetDirectorySize(): void
    {
        $subject = $this->buildSubject();

        $result = filesize($subject->path('directory'));

        self::assertEquals(0, $result);
    }

    public function testShouldGetSymlinkSize(): void
    {
        $subject = $this->buildSubject();

        $result = filesize($subject->path('symlink'));

        self::assertEquals(7, $result);
    }

    public function testShouldGetFileSizeWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = filesize($subject->path('filename'));

        self::assertEquals(7, $result);
    }
}

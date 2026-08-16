<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function filemtime;
use function time;

final class FilemtimeTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetFileTimestamp(): void
    {
        $subject = $this->buildSubject();

        $mtime = time() - 30;
        $subject->node('filename')->mtime = $mtime;

        $result = filemtime($subject->path('filename'));

        self::assertEquals($mtime, $result);
    }

    public function testShouldGetDirectoryTimestamp(): void
    {
        $subject = $this->buildSubject();

        $mtime = time() - 30;
        $subject->node('directory')->mtime = $mtime;

        $result = filemtime($subject->path('directory'));

        self::assertEquals($mtime, $result);
    }

    public function testShouldGetSymlinkActualFileTimestamp(): void
    {
        $subject = $this->buildSubject();

        $mtime = time() - 30;
        $subject->node('filename')->mtime = $mtime;

        $result = filemtime($subject->path('symlink'));

        self::assertEquals($mtime, $result);
    }

    public function testShouldGetFileTimestampWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;
        $mtime = time() - 30;
        $subject->node('filename')->mtime = $mtime;

        $result = filemtime($subject->path('filename'));

        self::assertEquals($mtime, $result);
    }
}

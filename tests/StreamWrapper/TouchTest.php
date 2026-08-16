<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function time;
use function touch;

final class TouchTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'symlink' => '@filename',
        ]);
    }

    public function testShouldUpdateFileModifiedTimestamp(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->mtime = time() - 60;

        $mtime = $subject->node('filename')->mtime;

        $result = touch($subject->path('filename'));

        self::assertTrue($result);
        self::assertGreaterThan($mtime, $subject->node('filename')->mtime);
    }

    public function testShouldUpdateSymlinkFileModifiedTimestamp(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->mtime = time() - 60;

        $mtime = $subject->node('filename')->mtime;
        $mtimeSymlink = $subject->node('symlink')->mtime;

        $result = touch($subject->path('symlink'));

        self::assertTrue($result);
        self::assertGreaterThan($mtime, $subject->node('filename')->mtime);
        self::assertEquals($mtimeSymlink, $subject->node('symlink')->mtime);
    }

    public function testShouldReturnFalseWhenModifyingTimestampWithoutWriteAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0555;
        $subject->node('filename')->mtime = time() - 60;

        $mtime = $subject->node('filename')->mtime;

        $result = touch($subject->path('filename'));

        self::assertFalse($result);
        self::assertEquals($mtime, $subject->node('filename')->mtime);
    }
}

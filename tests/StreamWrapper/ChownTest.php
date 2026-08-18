<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function chown;
use function time;

final class ChownTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldChangeFileOwner(): void
    {
        $subject = $this->buildSubject();

        $result = chown($subject->path('filename'), 2000);

        self::assertTrue($result);
        self::assertEquals(2000, $subject->node('filename')->userId);
    }

    public function testShouldChangeDirectoryOwner(): void
    {
        $subject = $this->buildSubject();

        $result = chown($subject->path('directory'), 2000);

        self::assertTrue($result);
        self::assertEquals(2000, $subject->node('directory')->userId);
    }

    public function testShouldChangeSymlinkOwner(): void
    {
        $subject = $this->buildSubject();

        $result = chown($subject->path('symlink'), 2000);

        self::assertTrue($result);
        self::assertEquals(2000, $subject->node('symlink')->userId);
    }

    public function testShouldReturnFalseWhenMissingWriteAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = chown($subject->path('filename'), 2000);

        self::assertFalse($result);
        self::assertEquals(1000, $subject->node('filename')->userId);
    }

    public function testShouldNotUpdateMTime(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->mtime = time() - 30;
        $mtime = $subject->node('filename')->mtime;

        chown($subject->path('filename'), 2000);

        self::assertEquals($mtime, $subject->node('filename')->mtime);
    }

    public function testShouldUpdateCTime(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->ctime = time() - 30;

        chown($subject->path('filename'), 2000);

        self::assertGreaterThanOrEqual(time() - 1, $subject->node('filename')->ctime);
    }

    public function testShouldNotUpdateATime(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->atime = time() - 30;
        $atime = $subject->node('filename')->atime;

        chown($subject->path('filename'), 2000);

        self::assertEquals($atime, $subject->node('filename')->atime);
    }
}

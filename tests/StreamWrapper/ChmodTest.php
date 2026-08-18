<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function chmod;
use function time;

final class ChmodTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
        ]);
    }

    public function testShouldChangeFilePermissions(): void
    {
        $subject = $this->buildSubject();

        $result = chmod($subject->path('filename'), 0777);

        self::assertTrue($result);
        self::assertEquals(0777, $subject->node('filename')->permissions);
    }

    public function testShouldChangeDirectoryPermissions(): void
    {
        $subject = $this->buildSubject();

        $result = chmod($subject->path('directory'), 0777);

        self::assertTrue($result);
        self::assertEquals(0777, $subject->node('directory')->permissions);
    }

    public function testShouldReturnFalseOnMissingWriteAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0755; // group cannot write
        $subject->node('filename')->userId = 2000; // file owned by someone else

        $result = chmod($subject->path('filename'), 0777);

        self::assertFalse($result);
        self::assertEquals(0755, $subject->node('filename')->permissions);
    }

    public function testShouldAlwaysAllowOwnerToChangePermissions(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = chmod($subject->path('filename'), 0777);

        self::assertTrue($result);
        self::assertEquals(0777, $subject->node('filename')->permissions);
    }

    public function testShouldNotUpdateATime(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->atime = time() - 30;
        $atime = $subject->node('filename')->atime;

        chmod($subject->path('filename'), 0777);

        self::assertEquals($atime, $subject->node('filename')->atime);
    }

    public function testShouldNotUpdateMTime(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->mtime = time() - 30;
        $mtime = $subject->node('filename')->mtime;

        chmod($subject->path('filename'), 0777);

        self::assertEquals($mtime, $subject->node('filename')->mtime);
    }

    public function testShouldUpdateCTime(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->ctime = time() - 30;

        chmod($subject->path('filename'), 0777);

        self::assertGreaterThanOrEqual(time() - 1, $subject->node('filename')->ctime);
    }
}

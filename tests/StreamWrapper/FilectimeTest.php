<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function filectime;
use function time;

final class FilectimeTest extends TestCase
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

        $ctime = time() - 30;
        $subject->node('filename')->ctime = $ctime;

        $result = filectime($subject->path('filename'));

        self::assertEquals($ctime, $result);
    }

    public function testShouldGetDirectoryTimestamp(): void
    {
        $subject = $this->buildSubject();

        $ctime = time() - 30;
        $subject->node('directory')->ctime = $ctime;

        $result = filectime($subject->path('directory'));

        self::assertEquals($ctime, $result);
    }

    public function testShouldGetSymlinkActualFileTimestamp(): void
    {
        $subject = $this->buildSubject();

        $ctime = time() - 30;
        $subject->node('filename')->ctime = $ctime;

        $result = filectime($subject->path('symlink'));

        self::assertEquals($ctime, $result);
    }

    public function testShouldGetFileTimestampWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;
        $ctime = time() - 30;
        $subject->node('filename')->ctime = $ctime;

        $result = filectime($subject->path('filename'));

        self::assertEquals($ctime, $result);
    }
}

<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function fileatime;
use function time;

final class FileatimeTest extends TestCase
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

        $atime = time() - 30;
        $subject->node('filename')->atime = $atime;

        $result = fileatime($subject->path('filename'));

        self::assertEquals($atime, $result);
    }

    public function testShouldGetDirectoryTimestamp(): void
    {
        $subject = $this->buildSubject();

        $atime = time() - 30;
        $subject->node('directory')->atime = $atime;

        $result = fileatime($subject->path('directory'));

        self::assertEquals($atime, $result);
    }

    public function testShouldGetSymlinkActualFileTimestamp(): void
    {
        $subject = $this->buildSubject();

        $atime = time() - 30;
        $subject->node('filename')->atime = $atime;

        $result = fileatime($subject->path('symlink'));

        self::assertEquals($atime, $result);
    }

    public function testShouldGetFileTimestampWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;
        $atime = time() - 30;
        $subject->node('filename')->atime = $atime;

        $result = fileatime($subject->path('filename'));

        self::assertEquals($atime, $result);
    }
}

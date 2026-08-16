<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function stat;

final class StatTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetFileStat(): void
    {
        $subject = $this->buildSubject();

        $result = stat($subject->path('filename'));

        self::assertIsArray($result);
    }

    public function testShouldGetDirectoryStat(): void
    {
        $subject = $this->buildSubject();

        $result = stat($subject->path('directory'));

        self::assertIsArray($result);
    }

    public function testShouldGetSymlinkStat(): void
    {
        $subject = $this->buildSubject();

        $result = stat($subject->path('symlink'));

        self::assertIsArray($result);
    }

    public function testShouldStillGetFileStatWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = stat($subject->path('filename'));

        self::assertIsArray($result);
    }
}

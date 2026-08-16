<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function lstat;

final class LstatTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetFileLstat(): void
    {
        $subject = $this->buildSubject();

        $result = lstat($subject->path('filename'));

        self::assertIsArray($result);
    }

    public function testShouldGetDirectoryLstat(): void
    {
        $subject = $this->buildSubject();

        $result = lstat($subject->path('directory'));

        self::assertIsArray($result);
    }

    public function testShouldGetSymlinkLstat(): void
    {
        $subject = $this->buildSubject();

        $result = lstat($subject->path('symlink'));

        self::assertIsArray($result);
    }

    public function testShouldStillGetFileLstatWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = lstat($subject->path('filename'));

        self::assertIsArray($result);
    }
}

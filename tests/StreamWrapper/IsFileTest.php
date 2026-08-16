<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function is_file;

final class IsFileTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetFileIsAFile(): void
    {
        $subject = $this->buildSubject();

        $result = is_file($subject->path('filename'));

        self::assertTrue($result);
    }

    public function testShouldNotGetDirectoryIsAFile(): void
    {
        $subject = $this->buildSubject();

        $result = is_file($subject->path('directory'));

        self::assertFalse($result);
    }

    public function testShouldGetSymlinkIsAFile(): void
    {
        $subject = $this->buildSubject();

        $result = is_file($subject->path('symlink'));

        self::assertTrue($result);
    }

    public function testShouldStillGetFileIsFileWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = is_file($subject->path('filename'));

        self::assertTrue($result);
    }
}

<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function file_exists;

final class FileExistsTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldCheckFileExists(): void
    {
        $subject = $this->buildSubject();

        $result = file_exists($subject->path('filename'));

        self::assertTrue($result);
    }

    public function testShouldCheckDirectoryExists(): void
    {
        $subject = $this->buildSubject();

        $result = file_exists($subject->path('directory'));

        self::assertTrue($result);
    }

    public function testShouldCheckSymlinkExists(): void
    {
        $subject = $this->buildSubject();

        $result = file_exists($subject->path('symlink'));

        self::assertTrue($result);
    }

    public function testShouldCheckNonExistentNodeExists(): void
    {
        $subject = $this->buildSubject();

        $result = file_exists($subject->path('z'));

        self::assertFalse($result);
    }
}

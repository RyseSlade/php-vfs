<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function is_executable;

final class IsExecutableTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetFileIsExecutable(): void
    {
        $subject = $this->buildSubject();

        $result = is_executable($subject->path('filename'));

        self::assertTrue($result);
    }

    public function testShouldGetDirectoryIsExecutable(): void
    {
        $subject = $this->buildSubject();

        $result = is_executable($subject->path('directory'));

        self::assertTrue($result);
    }

    public function testShouldGetSymlinkIsExecutable(): void
    {
        $subject = $this->buildSubject();

        $result = is_executable($subject->path('symlink'));

        self::assertTrue($result);
    }

    public function testShouldReturnFalseWithoutExecutableAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0666;

        $result = is_executable($subject->path('filename'));

        self::assertFalse($result);
    }
}

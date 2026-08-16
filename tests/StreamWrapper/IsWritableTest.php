<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function is_writable;

final class IsWritableTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldCheckFileIsWritable(): void
    {
        $subject = $this->buildSubject();

        $result = is_writable($subject->path('filename'));

        self::assertTrue($result);
    }

    public function testShouldCheckDirectoryIsWritable(): void
    {
        $subject = $this->buildSubject();

        $result = is_writable($subject->path('directory'));

        self::assertTrue($result);
    }

    public function testShouldCheckSymlinkFileIsWritable(): void
    {
        $subject = $this->buildSubject();

        $result = is_writable($subject->path('symlink'));

        self::assertTrue($result);
    }

    public function testShouldCheckSymlinkFileIsNotWritable(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = is_writable($subject->path('symlink'));

        self::assertFalse($result);
    }

    public function testShouldCheckNonExistantFileIsWritable(): void
    {
        $subject = $this->buildSubject();

        $result = is_writable($subject->path('z'));

        self::assertFalse($result);
    }

    public function testShouldReturnFalseWhenMissingWriteAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = is_writable($subject->path('filename'));

        self::assertFalse($result);
    }
}

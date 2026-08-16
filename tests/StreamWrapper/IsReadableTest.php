<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function is_readable;

final class IsReadableTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldCheckFileIsReadable(): void
    {
        $subject = $this->buildSubject();

        $result = is_readable($subject->path('filename'));

        self::assertTrue($result);
    }

    public function testShouldCheckDirectoryIsReadable(): void
    {
        $subject = $this->buildSubject();

        $result = is_readable($subject->path('directory'));

        self::assertTrue($result);
    }

    public function testShouldCheckSymlinkFileIsReadable(): void
    {
        $subject = $this->buildSubject();

        $result = is_readable($subject->path('symlink'));

        self::assertTrue($result);
    }

    public function testShouldCheckSymlinkFileIsNotReadable(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = is_readable($subject->path('symlink'));

        self::assertFalse($result);
    }

    public function testShouldCheckNonExistantFileIsNotReadable(): void
    {
        $subject = $this->buildSubject();

        $result = is_readable($subject->path('z'));

        self::assertFalse($result);
    }

    public function testShouldFileWithoutReadAccessIsNotReadable(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = is_readable($subject->path('filename'));

        self::assertFalse($result);
    }
}

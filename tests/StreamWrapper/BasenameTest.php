<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function basename;

final class BasenameTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetFileBasename(): void
    {
        $subject = $this->buildSubject();

        $result = basename($subject->path('filename'));

        self::assertEquals('filename', $result);
    }

    public function testShouldGetDirectoryBasename(): void
    {
        $subject = $this->buildSubject();

        $result = basename($subject->path('directory'));

        self::assertEquals('directory', $result);
    }

    public function testShouldGetSymlinkBasename(): void
    {
        $subject = $this->buildSubject();

        $result = basename($subject->path('symlink'));

        self::assertEquals('symlink', $result);
    }

    public function testShouldGetBasenameOfNonExistantFile(): void
    {
        $subject = $this->buildSubject();

        $result = basename($subject->path('z'));

        self::assertEquals('z', $result);
    }

    public function testShouldGetBasenameWhenMissingAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = basename($subject->path('filename'));

        self::assertEquals('filename', $result);
    }
}

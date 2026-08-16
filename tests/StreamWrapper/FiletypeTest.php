<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function filetype;

final class FiletypeTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetFileType(): void
    {
        $subject = $this->buildSubject();

        $result = filetype($subject->path('filename'));

        self::assertEquals('file', $result);
    }

    public function testShouldGetDirectoryType(): void
    {
        $subject = $this->buildSubject();

        $result = filetype($subject->path('directory'));

        self::assertEquals('dir', $result);
    }

    public function testShouldGetSymlinkType(): void
    {
        $subject = $this->buildSubject();

        $result = filetype($subject->path('symlink'));

        self::assertEquals('link', $result);
    }

    public function testShouldGetGetFileTypeWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = filetype($subject->path('filename'));

        self::assertEquals('file', $result);
    }
}

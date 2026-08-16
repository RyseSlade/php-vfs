<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function file;

final class FileTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => "line1\nline2\nline3",
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetFileContentAsArray(): void
    {
        $subject = $this->buildSubject();

        $content = file($subject->path('filename'));

        self::assertEquals(["line1\n", "line2\n", "line3"], $content);
    }

    public function testShouldReturnFalseForDirectory(): void
    {
        $subject = $this->buildSubject();

        $content = file($subject->path('directory'));

        // actually returns empty array
        self::assertFalse($content);
    }

    public function testShouldReturnFalseOnSymlink(): void
    {
        $subject = $this->buildSubject();

        $content = file($subject->path('symlink'));

        self::assertEquals(["line1\n", "line2\n", "line3"], $content);
    }

    public function testShouldReturnFalseOnMissingAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $content = file($subject->path('filename'));

        self::assertFalse($content);
    }
}

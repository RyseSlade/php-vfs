<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function ob_get_clean;
use function ob_start;
use function readfile;

final class ReadfileTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetBytesReadOfFile(): void
    {
        $subject = $this->buildSubject();

        ob_start();

        $result = readfile($subject->path('filename'));

        $content = ob_get_clean();

        self::assertEquals(7, $result);
        self::assertEquals('content', $content);
    }

    public function testShouldReturnFalseForDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = readfile($subject->path('directory'));

        self::assertFalse($result);
    }

    public function testShouldReturnFalseForFileWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = readfile($subject->path('filename'));

        self::assertFalse($result);
    }

    public function testShouldReturnFalseForSymlink(): void
    {
        $subject = $this->buildSubject();

        ob_start();

        $result = readfile($subject->path('symlink'));

        $content = ob_get_clean();

        self::assertEquals(7, $result);
        self::assertEquals('content', $content);
    }
}

<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function time;

final class FileGetContentsTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetFileContent(): void
    {
        $subject = $this->buildSubject();

        $content = file_get_contents($subject->path('filename'));

        self::assertEquals('content', $content);
    }

    public function testShouldReturnFalseForDirectory(): void
    {
        $subject = $this->buildSubject();

        $content = file_get_contents($subject->path('directory'));

        self::assertFalse($content);
    }

    public function testShouldGetSymlinkFileContent(): void
    {
        $subject = $this->buildSubject();

        $content = file_get_contents($subject->path('symlink'));

        self::assertEquals('content', $content);
    }

    public function testShouldReturnFalseOnMissingReadAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('directory')->permissions = 0000;

        $content = file_get_contents($subject->path('directory'));

        self::assertFalse($content);
    }

    public function testShouldNotUpdateMTime(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->mtime = time() - 30;
        $mtime = $subject->node('filename')->mtime;

        file_get_contents($subject->path('filename'));

        self::assertEquals($mtime, $subject->node('filename')->mtime);
    }

    public function testShouldUpdateATime(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->atime = time() - 30;

        file_get_contents($subject->path('filename'));

        self::assertGreaterThanOrEqual(time() - 1, $subject->node('filename')->atime);
    }
}

<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function pathinfo;

final class PathinfoTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => '',
            'file.txt' => '',
            'directory' => [
                'file' => '',
            ],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetPathinfoForFile(): void
    {
        $subject = $this->buildSubject();

        $result = pathinfo($subject->path('filename'));

        self::assertEquals([
            'dirname' => 'vfs://root',
            'basename' => 'filename',
            'filename' => 'filename',
        ], $result);
    }

    public function testShouldGetPathinfoForFileWithExtension(): void
    {
        $subject = $this->buildSubject();

        $result = pathinfo($subject->path('file.txt'));

        self::assertEquals([
            'dirname' => 'vfs://root',
            'basename' => 'file.txt',
            'filename' => 'file',
            'extension' => 'txt',
        ], $result);
    }

    public function testShouldGetPathinfoForDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = pathinfo($subject->path('directory'));

        self::assertEquals([
            'dirname' => 'vfs://root',
            'basename' => 'directory',
            'filename' => 'directory',
        ], $result);
    }

    public function testShouldGetPathinfoForFileInDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = pathinfo($subject->path('directory/file'));

        self::assertEquals([
            'dirname' => 'vfs://root/directory',
            'basename' => 'file',
            'filename' => 'file',
        ], $result);
    }

    public function testShouldGetPathinfoForSymlink(): void
    {
        $subject = $this->buildSubject();

        $result = pathinfo($subject->path('symlink'));

        self::assertEquals([
            'dirname' => 'vfs://root',
            'basename' => 'symlink',
            'filename' => 'symlink',
        ], $result);
    }
}

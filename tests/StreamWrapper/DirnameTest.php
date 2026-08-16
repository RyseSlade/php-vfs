<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function dirname;

final class DirnameTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [
                'file' => '',
            ],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetDirnameOfFile(): void
    {
        $subject = $this->buildSubject();

        $result = dirname($subject->path('filename'));

        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH, $result);
    }

    public function testShouldGetDirnameOfDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = dirname($subject->path('directory'));

        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH, $result);
    }

    public function testShouldGetDirnameOfSymlink(): void
    {
        $subject = $this->buildSubject();

        $result = dirname($subject->path('symlink'));

        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH, $result);
    }

    public function testShouldGetDirnameOfFileInDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = dirname($subject->path('directory/file'));

        self::assertEquals($subject->path('directory'), $result);
    }
}

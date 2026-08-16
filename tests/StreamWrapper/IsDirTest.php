<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\SymlinkNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function is_dir;

final class IsDirTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldCheckFileIsDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = is_dir($subject->path('filename'));

        self::assertFalse($result);
    }

    public function testShouldCheckDirectoryIsDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = is_dir($subject->path('directory'));

        self::assertTrue($result);
    }

    public function testShouldCheckSymlinkFileIsDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = is_dir($subject->path('symlink'));

        self::assertFalse($result);
    }

    public function testShouldCheckSymlinkDirectoryIsDirectory(): void
    {
        $subject = $this->buildSubject();

        /** @var SymlinkNode $node */
        $node = $subject->node('symlink');

        $node->linkTarget = $subject->node('directory');

        $result = is_dir($subject->path('symlink'));

        self::assertTrue($result);
    }

    public function testShouldReturnFalseWhenMissingReadAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = is_dir($subject->path('filename'));

        self::assertFalse($result);
    }
}

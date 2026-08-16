<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\DirectoryNode;
use Aedon\VFS\EmptyNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function mkdir;

final class MkdirTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'directory' => [],
            'filename' => '',
            'symlink' => '@directory',
        ]);
    }

    public function testShouldCreateDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = mkdir($subject->path('newdirectory'), 0555);

        self::assertTrue($result);
        self::assertEquals(0555, $subject->node('newdirectory')->permissions);
    }

    public function testShouldCreateChildDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = mkdir($subject->path('directory/subdirectory'), 0555);

        self::assertTrue($result);
        self::assertInstanceOf(DirectoryNode::class, $subject->node('directory/subdirectory'));
        self::assertEquals(0555, $subject->node('directory/subdirectory')->permissions);
    }

    public function testShouldNotCreateChildDirectoryWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('directory')->permissions = 0000;

        $result = mkdir($subject->path('directory/subdirectory'));

        self::assertFalse($result);
        self::assertInstanceOf(EmptyNode::class, $subject->node('directory/subdirectory'));
    }

    public function testShouldNotCreateChildDirectoryWithoutRecursive(): void
    {
        $subject = $this->buildSubject();

        $result = mkdir($subject->path('directory/subdirectory/nextdirectory'));

        self::assertFalse($result);
    }

    public function testShouldCreateChildDirectoryWithRecursive(): void
    {
        $subject = $this->buildSubject();

        $result = mkdir($subject->path('directory/subdirectory/nextdirectory'), 0755, true);

        self::assertTrue($result);
        self::assertInstanceOf(DirectoryNode::class, $subject->node('directory/subdirectory'));
        self::assertInstanceOf(DirectoryNode::class, $subject->node('directory/subdirectory/nextdirectory'));
    }

    public function testShouldNotCreateExistingDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = mkdir($subject->path('directory'));

        self::assertFalse($result);
    }

    public function testShouldNotCreateDirectoryInFile(): void
    {
        $subject = $this->buildSubject();

        $result = mkdir($subject->path('filename'));

        self::assertFalse($result);
    }

    public function testShouldNotCreateDirectoryInSymlink(): void
    {
        $subject = $this->buildSubject();

        $result = mkdir($subject->path('symlink'));

        self::assertFalse($result);
    }
}

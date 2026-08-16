<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function lchown;

final class LchownTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldChangeFileOwner(): void
    {
        $subject = $this->buildSubject();

        $result = lchown($subject->path('filename'), 2000);

        self::assertTrue($result);
        self::assertEquals(2000, $subject->node('filename')->userId);
    }

    public function testShouldChangeDirectoryOwner(): void
    {
        $subject = $this->buildSubject();

        $result = lchown($subject->path('directory'), 2000);

        self::assertTrue($result);
        self::assertEquals(2000, $subject->node('directory')->userId);
    }

    public function testShouldChangeSymlinkOwner(): void
    {
        $subject = $this->buildSubject();

        $result = lchown($subject->path('symlink'), 2000);

        self::assertTrue($result);
        self::assertEquals(2000, $subject->node('symlink')->userId);
    }

    public function testShouldReturnFalseWhenMissingWriteAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = lchown($subject->path('filename'), 2000);

        self::assertFalse($result);
        self::assertEquals(1000, $subject->node('filename')->userId);
    }
}

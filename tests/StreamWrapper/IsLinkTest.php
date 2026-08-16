<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function is_link;

final class IsLinkTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldNotGetFileIsALink(): void
    {
        $subject = $this->buildSubject();

        $result = is_link($subject->path('filename'));

        self::assertFalse($result);
    }

    public function testShouldNotGetDirectoryIsALink(): void
    {
        $subject = $this->buildSubject();

        $result = is_link($subject->path('directory'));

        self::assertFalse($result);
    }

    public function testShouldGetSymlinkIsALink(): void
    {
        $subject = $this->buildSubject();

        $result = is_link($subject->path('symlink'));

        self::assertTrue($result);
    }

    public function testShouldGetSymlinkIsALinkWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('symlink')->permissions = 0000;

        $result = is_link($subject->path('symlink'));

        self::assertTrue($result);
    }
}

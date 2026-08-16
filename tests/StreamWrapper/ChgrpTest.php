<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function chgrp;

final class ChgrpTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldChangeFileGroup(): void
    {
        $subject = $this->buildSubject();

        $result = chgrp($subject->path('filename'), 2000);

        self::assertTrue($result);
        self::assertEquals(2000, $subject->node('filename')->groupId);
    }

    public function testShouldChangeDirectoryGroup(): void
    {
        $subject = $this->buildSubject();

        $result = chgrp($subject->path('directory'), 2000);

        self::assertTrue($result);
        self::assertEquals(2000, $subject->node('directory')->groupId);
    }

    public function testShouldChangeSymlinkGroup(): void
    {
        $subject = $this->buildSubject();

        $result = chgrp($subject->path('symlink'), 2000);

        self::assertTrue($result);
        self::assertEquals(2000, $subject->node('symlink')->groupId);
        self::assertEquals(1000, $subject->node('filename')->groupId);
    }

    public function testShouldReturnFalseWhenMissingWriteAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = chgrp($subject->path('filename'), 2000);

        self::assertFalse($result);
        self::assertEquals(1000, $subject->node('filename')->groupId);
    }
}

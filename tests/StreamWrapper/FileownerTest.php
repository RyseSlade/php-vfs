<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function fileowner;

final class FileownerTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldGetFileownerOfFile(): void
    {
        $subject = $this->buildSubject();

        $result = fileowner($subject->path('filename'));

        self::assertEquals(1000, $result);
    }

    public function testShouldGetFileownerOfDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = fileowner($subject->path('directory'));

        self::assertEquals(1000, $result);
    }

    public function testShouldGetFileownerOfSymlinkActualFile(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->userId = 2000;

        $result = fileowner($subject->path('symlink'));

        self::assertEquals(2000, $result);
    }

    public function testShouldGetFileownerWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = fileowner($subject->path('filename'));

        self::assertEquals(1000, $result);
    }
}

<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use Directory;
use PHPUnit\Framework\TestCase;

use function dir;

final class DirTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'directory' => [
                'filename' => '',
            ],
        ]);
    }

    public function testShouldChangeDirectory(): void
    {
        $subject = $this->buildSubject();

        $directory = dir($subject->path('directory'));

        self::assertInstanceOf(Directory::class, $directory);

        $result = $directory->read();

        self::assertEquals('.', $result);

        $result = $directory->read();

        self::assertEquals('..', $result);

        $result = $directory->read();

        self::assertEquals('filename', $result);

        $result = $directory->read();

        self::assertFalse($result);

        $directory->close();
    }
}

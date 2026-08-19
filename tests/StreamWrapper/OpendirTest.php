<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function closedir;
use function opendir;
use function readdir;
use function rewinddir;

final class OpendirTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'directory' => [
                'file1' => '',
                'file2' => '',
            ],
        ]);
    }

    public function testShouldOpenDirectoryAndReadContent(): void
    {
        $subject = $this->buildSubject();

        $fp = opendir($subject->path('directory'));

        self::assertIsResource($fp);

        $result = readdir($fp);

        self::assertEquals('.', $result);

        $result = readdir($fp);

        self::assertEquals('..', $result);

        $result = readdir($fp);

        self::assertEquals('file1', $result);

        $result = readdir($fp);

        self::assertEquals('file2', $result);

        $result = readdir($fp);

        self::assertFalse($result);

        rewinddir($fp);

        $result = readdir($fp);

        self::assertEquals('.', $result);

        $result = readdir($fp);

        self::assertEquals('..', $result);

        $result = readdir($fp);

        self::assertEquals('file1', $result);

        $result = readdir($fp);

        self::assertEquals('file2', $result);

        closedir($fp);
    }
}

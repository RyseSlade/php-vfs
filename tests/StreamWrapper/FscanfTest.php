<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function fopen;
use function fscanf;

final class FscanfTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'test 10',
            'symlink' => '@filename',
        ]);
    }

    public function testShouldParseInput(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp = fopen($file, 'r');

        self::assertIsResource($fp);

        $result = fscanf($fp, '%s %D');

        self::assertEquals([
            'test',
            10
        ], $result);
    }
}

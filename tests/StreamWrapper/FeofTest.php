<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function feof;
use function fopen;
use function fseek;

final class FeofTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
        ]);
    }

    public function testShouldCheckForEndOfFile(): void
    {
        $subject = $this->buildSubject();

        $fp = fopen($subject->path('filename'), 'r');

        self::assertIsResource($fp);

        $result = feof($fp);

        self::assertFalse($result);

        fseek($fp, 0, SEEK_END);

        $result = feof($fp);

        self::assertTrue($result);
    }
}

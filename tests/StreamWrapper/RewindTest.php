<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function fgets;
use function fopen;
use function fseek;
use function rewind;

final class RewindTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
        ]);
    }

    public function testShouldRewindStream(): void
    {
        $subject = $this->buildSubject();

        $fp = fopen($subject->path('filename'), 'r');

        self::assertIsResource($fp);

        fseek($fp, -1, SEEK_END);

        $result = fgets($fp, 2);

        self::assertEquals('t', $result);

        $result = rewind($fp);

        self::assertTrue($result);

        $result = fgets($fp, 10);

        self::assertEquals('content', $result);
    }
}

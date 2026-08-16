<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function fgets;
use function fopen;
use function fseek;

final class FseekTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
        ]);
    }

    public function testShouldSetPositionInFile(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp = fopen($file, 'r');

        self::assertIsResource($fp);

        fseek($fp, 3, SEEK_SET);

        $content = fgets($fp);

        self::assertEquals('tent', $content);
    }

    public function testShouldSetPositionInFileFromCurrentPosition(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp = fopen($file, 'r');

        self::assertIsResource($fp);

        fseek($fp, 2, SEEK_SET);
        fseek($fp, 2, SEEK_CUR);

        $content = fgets($fp);

        self::assertEquals('ent', $content);
    }

    public function testShouldSetPositionInFileFromEnd(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp = fopen($file, 'r');

        self::assertIsResource($fp);

        fseek($fp, -2, SEEK_END);

        $content = fgets($fp);

        self::assertEquals('nt', $content);
    }
}

<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fgetc;
use function fopen;

final class FgetcTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
        ]);
    }

    public function testShouldReadContent(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp = fopen($file, 'r');

        self::assertIsResource($fp);

        $content = fgetc($fp);

        self::assertEquals('c', $content);

        fclose($fp);
    }

    public function testShouldReadContentWithLength(): void
    {
        $subject = $this->buildSubject();

        $fp = fopen($subject->path('filename'), 'r');

        self::assertIsResource($fp);

        $content = fgetc($fp);

        self::assertEquals('c', $content);

        $content = fgetc($fp);

        self::assertEquals('o', $content);

        $content = fgetc($fp);

        self::assertEquals('n', $content);

        $content = fgetc($fp);

        self::assertEquals('t', $content);

        $content = fgetc($fp);

        self::assertEquals('e', $content);

        $content = fgetc($fp);

        self::assertEquals('n', $content);

        $content = fgetc($fp);

        self::assertEquals('t', $content);

        $content = fgetc($fp);

        self::assertFalse($content);

        fclose($fp);
    }
}

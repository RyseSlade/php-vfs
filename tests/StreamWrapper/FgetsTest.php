<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fgets;
use function fopen;

final class FgetsTest extends TestCase
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

        $content = fgets($fp);

        self::assertEquals('content', $content);

        fclose($fp);
    }

    public function testShouldReadContentWithLength(): void
    {
        $subject = $this->buildSubject();

        $fp = fopen($subject->path('filename'), 'r');

        self::assertIsResource($fp);

        $content = fgets($fp, 3);

        self::assertEquals('co', $content);

        $content = fgets($fp, 3);

        self::assertEquals('nt', $content);

        $content = fgets($fp, 3);

        self::assertEquals('en', $content);

        $content = fgets($fp, 3);

        self::assertEquals('t', $content);

        $content = fgets($fp, 3);

        self::assertFalse($content);

        fclose($fp);
    }
}

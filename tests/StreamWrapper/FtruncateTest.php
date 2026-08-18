<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\FileNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function fgets;
use function fopen;
use function fseek;
use function ftell;
use function ftruncate;

final class FtruncateTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
        ]);
    }

    public function testShouldTruncateFile(): void
    {
        $subject = $this->buildSubject();

        $fp = fopen($subject->path('filename'), 'a');

        self::assertIsResource($fp);

        $result = ftruncate($fp, 3);

        self::assertTrue($result);

        /** @var FileNode $node */
        $node = $subject->node('filename');

        self::assertEquals('con', $node->content);
        self::assertEquals(3, $node->size);
    }

    public function testShouldNotUpdateFilePointer(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp = fopen($file, 'a');

        self::assertIsResource($fp);

        fseek($fp, 3);

        $result = ftell($fp);

        self::assertEquals(3, $result);

        $result = ftruncate($fp, 5);

        self::assertTrue($result);

        $result = ftell($fp);

        self::assertEquals(3, $result);

        $result = fgets($fp);

        self::assertEquals('te', $result);
    }

    public function testShouldAddNullBytesWhenSizeIsIncreased(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp = fopen($file, 'r+');

        self::assertIsResource($fp);

        $result = ftruncate($fp, 10);

        self::assertTrue($result);

        /** @var FileNode $node */
        $node = $subject->node('filename');

        self::assertEquals("content\0\0\0", $node->content);
    }
}

<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\FileNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function fwrite;

final class FwriteTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
        ]);
    }

    public function testShouldWriteToFile(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp = fopen($file, 'w');

        self::assertIsResource($fp);

        $result = fwrite($fp, 'content');

        self::assertEquals(7, $result);

        /** @var FileNode $node */
        $node = $subject->node('filename');

        self::assertEquals('content', $node->content);

        fclose($fp);
    }

    public function testShouldWriteSpecificLengthToFile(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp = fopen($file, 'w');

        self::assertIsResource($fp);

        $result = fwrite($fp, 'content', 3);

        self::assertEquals(3, $result);

        /** @var FileNode $node */
        $node = $subject->node('filename');

        self::assertEquals('con', $node->content);

        $result = fwrite($fp, 'tent', 3);

        self::assertEquals(3, $result);

        /** @var FileNode $node */
        $node = $subject->node('filename');

        self::assertEquals('conten', $node->content);

        fclose($fp);
    }
}

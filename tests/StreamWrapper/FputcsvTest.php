<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\FileNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function fputcsv;

final class FputcsvTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => '',
        ]);
    }

    public function testShouldPutCsvToFile(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp = fopen($file, 'w');

        self::assertIsResource($fp);

        $result = fputcsv($fp, ['a', 1], ',', '"', '\\');

        self::assertEquals(4, $result);

        $result = fputcsv($fp, ['b', 2], ',', '"', '\\');

        self::assertEquals(4, $result);

        /** @var FileNode $node */
        $node = $subject->node('filename');

        self::assertEquals("a,1\nb,2\n", $node->content);

        fclose($fp);
    }
}

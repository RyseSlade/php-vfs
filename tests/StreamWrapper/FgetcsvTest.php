<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fgetcsv;
use function fopen;

final class FgetcsvTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => "a,1\nb,2\nc,3\n",
        ]);
    }

    public function testShouldParseCsvFile(): void
    {
        $subject = $this->buildSubject();

        $fp = fopen($subject->path('filename'), 'r');

        self::assertIsResource($fp);

        $rows = [];

        while ($row = fgetcsv($fp, null, ',', '"', '\\')) {
            $rows[] = $row;
        }

        fclose($fp);

        self::assertEquals([
            ['a', '1'],
            ['b', '2'],
            ['c', '3'],
        ], $rows);
    }
}

<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function scandir;

final class ScandirTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'directory' => [
                'file1' => '',
                'file2' => '',
            ],
        ]);
    }

    public function testShouldGetDirectoryContent(): void
    {
        $subject = $this->buildSubject();

        $result = scandir($subject->path('directory'));

        self::assertEquals(['.', '..', 'file1', 'file2'], $result);
    }

    public function testShouldGetSortedDirectoryContent(): void
    {
        $subject = $this->buildSubject();

        $result = scandir($subject->path('directory'), SCANDIR_SORT_DESCENDING);

        self::assertEquals(['file2', 'file1', '..', '.'], $result);
    }
}

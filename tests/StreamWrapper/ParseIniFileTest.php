<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function parse_ini_file;

final class ParseIniFileTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'a=1',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldParseIniFile(): void
    {
        $subject = $this->buildSubject();

        $result = parse_ini_file($subject->path('filename'));

        self::assertEquals(['a' => 1], $result);
    }

    public function testShouldReturnFalseWhenParseIniFileFromDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = parse_ini_file($subject->path('directory'));

        self::assertFalse($result);
    }

    public function testShouldReturnFalseWhenParseIniFileFromSymlink(): void
    {
        $subject = $this->buildSubject();

        $result = parse_ini_file($subject->path('symlink'));

        self::assertEquals(['a' => 1], $result);
    }
}

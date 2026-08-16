<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

final class SplFileInfoTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'file.txt' => 'content',
            'directory' => [],
            'symlink' => '@file.txt',
        ]);
    }

    public function testShouldHandleSplFileObjectForFile(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('file.txt');
        $node = $subject->node('file.txt');

        $splFileInfo = new SplFileInfo($file);

        self::assertTrue($splFileInfo->isFile());
        self::assertFalse($splFileInfo->isDir());
        self::assertTrue($splFileInfo->isReadable());
        self::assertTrue($splFileInfo->isWritable());
        self::assertTrue($splFileInfo->isExecutable());
        self::assertFalse($splFileInfo->isLink());
        self::assertEquals('file.txt', $splFileInfo->getFilename());
        self::assertEquals('vfs://root', $splFileInfo->getPath());
        self::assertEquals('txt', $splFileInfo->getExtension());
        self::assertEquals($node->size, $splFileInfo->getSize());
        self::assertEquals('file', $splFileInfo->getType());
        self::assertEquals($node->groupId, $splFileInfo->getGroup());
        self::assertEquals($node->userId, $splFileInfo->getOwner());
        self::assertEquals($node->mtime, $splFileInfo->getMTime());
        self::assertEquals($node->atime, $splFileInfo->getATime());
        self::assertEquals($node->ctime, $splFileInfo->getCTime());
        self::assertEquals('vfs://root/file.txt', $splFileInfo->getPathname());
        self::assertEquals('file.txt', $splFileInfo->getBasename());
        self::assertEquals('', $splFileInfo->getRealPath()); // not supported
    }

    public function testShouldHandleSplFileObjectForDirectory(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('directory');
        $node = $subject->node('directory');

        $splFileInfo = new SplFileInfo($file);

        self::assertFalse($splFileInfo->isFile());
        self::assertTrue($splFileInfo->isDir());
        self::assertTrue($splFileInfo->isReadable());
        self::assertTrue($splFileInfo->isWritable());
        self::assertTrue($splFileInfo->isExecutable());
        self::assertFalse($splFileInfo->isLink());
        self::assertEquals('directory', $splFileInfo->getFilename());
        self::assertEquals('vfs://root', $splFileInfo->getPath());
        self::assertEquals('', $splFileInfo->getExtension());
        self::assertEquals($node->size, $splFileInfo->getSize());
        self::assertEquals('dir', $splFileInfo->getType());
        self::assertEquals($node->groupId, $splFileInfo->getGroup());
        self::assertEquals($node->userId, $splFileInfo->getOwner());
        self::assertEquals($node->mtime, $splFileInfo->getMTime());
        self::assertEquals($node->atime, $splFileInfo->getATime());
        self::assertEquals($node->ctime, $splFileInfo->getCTime());
        self::assertEquals('vfs://root/directory', $splFileInfo->getPathname());
        self::assertEquals('directory', $splFileInfo->getBasename());
        self::assertEquals('', $splFileInfo->getRealPath()); // not supported
    }

    public function testShouldHandleSplFileObjectForSymlink(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('symlink');
        $node = $subject->node('symlink');

        $splFileInfo = new SplFileInfo($file);

        self::assertTrue($splFileInfo->isFile());
        self::assertFalse($splFileInfo->isDir());
        self::assertTrue($splFileInfo->isReadable());
        self::assertTrue($splFileInfo->isWritable());
        self::assertTrue($splFileInfo->isExecutable());
        self::assertTrue($splFileInfo->isLink());
        self::assertEquals('symlink', $splFileInfo->getFilename());
        self::assertEquals('vfs://root', $splFileInfo->getPath());
        self::assertEquals('', $splFileInfo->getExtension());
        self::assertEquals(7, $splFileInfo->getSize());
        self::assertEquals('link', $splFileInfo->getType());
        self::assertEquals($node->groupId, $splFileInfo->getGroup());
        self::assertEquals($node->userId, $splFileInfo->getOwner());
        self::assertEquals($node->mtime, $splFileInfo->getMTime());
        self::assertEquals($node->atime, $splFileInfo->getATime());
        self::assertEquals($node->ctime, $splFileInfo->getCTime());
        self::assertEquals('vfs://root/symlink', $splFileInfo->getPathname());
        self::assertEquals('symlink', $splFileInfo->getBasename());
        self::assertEquals('', $splFileInfo->getRealPath()); // not supported
    }
}

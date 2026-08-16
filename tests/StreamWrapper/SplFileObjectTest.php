<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;
use SplFileObject;

final class SplFileObjectTest extends TestCase
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

        $splFileObject = new SplFileObject($file);

        self::assertTrue($splFileObject->isFile());
        self::assertFalse($splFileObject->isDir());
        self::assertTrue($splFileObject->isReadable());
        self::assertTrue($splFileObject->isWritable());
        self::assertTrue($splFileObject->isExecutable());
        self::assertFalse($splFileObject->isLink());
        self::assertEquals('file.txt', $splFileObject->getFilename());
        self::assertEquals('vfs://root', $splFileObject->getPath());
        self::assertEquals('txt', $splFileObject->getExtension());
        self::assertEquals($node->size, $splFileObject->getSize());
        self::assertEquals('file', $splFileObject->getType());
        self::assertEquals($node->groupId, $splFileObject->getGroup());
        self::assertEquals($node->userId, $splFileObject->getOwner());
        self::assertEquals($node->mtime, $splFileObject->getMTime());
        self::assertEquals($node->atime, $splFileObject->getATime());
        self::assertEquals($node->ctime, $splFileObject->getCTime());
        self::assertEquals('vfs://root/file.txt', $splFileObject->getPathname());
        self::assertEquals('file.txt', $splFileObject->getBasename());
        self::assertEquals('', $splFileObject->getRealPath()); // not supported
    }

    public function testShouldHandleSplFileObjectForSymlink(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('symlink');
        $node = $subject->node('symlink');

        $splFileObject = new SplFileObject($file);

        self::assertTrue($splFileObject->isFile());
        self::assertFalse($splFileObject->isDir());
        self::assertTrue($splFileObject->isReadable());
        self::assertTrue($splFileObject->isWritable());
        self::assertTrue($splFileObject->isExecutable());
        self::assertTrue($splFileObject->isLink());
        self::assertEquals('symlink', $splFileObject->getFilename());
        self::assertEquals('vfs://root', $splFileObject->getPath());
        self::assertEquals('', $splFileObject->getExtension());
        self::assertEquals(7, $splFileObject->getSize());
        self::assertEquals('link', $splFileObject->getType());
        self::assertEquals($node->groupId, $splFileObject->getGroup());
        self::assertEquals($node->userId, $splFileObject->getOwner());
        self::assertEquals($node->mtime, $splFileObject->getMTime());
        self::assertEquals($node->atime, $splFileObject->getATime());
        self::assertEquals($node->ctime, $splFileObject->getCTime());
        self::assertEquals('vfs://root/symlink', $splFileObject->getPathname());
        self::assertEquals('symlink', $splFileObject->getBasename());
        self::assertEquals('', $splFileObject->getRealPath()); // not supported
    }
}

<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\DirectoryNode;
use Aedon\VFS\FileNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function error_reporting;
use function file_put_contents;
use function restore_error_handler;
use function set_error_handler;
use function time;

final class FilePutContentsTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
            'directory' => [],
            'symlink' => '@filename',
        ]);
    }

    public function testShouldWriteContentToFile(): void
    {
        $subject = $this->buildSubject();

        $result = file_put_contents($subject->path('filename'), 'new content');

        self::assertEquals(11, $result);

        /** @var FileNode $node */
        $node = $subject->node('filename');

        self::assertEquals('new content', $node->content);
        self::assertEquals(11, $node->size);
    }

    public function testShouldAppendContentToFile(): void
    {
        $subject = $this->buildSubject();

        $result = file_put_contents($subject->path('filename'), 'new content', FILE_APPEND);

        self::assertEquals(11, $result);

        /** @var FileNode $node */
        $node = $subject->node('filename');

        self::assertEquals('contentnew content', $node->content);
    }

    public function testShouldAppendContentToFileWithLock(): void
    {
        $subject = $this->buildSubject();

        $result = file_put_contents($subject->path('filename'), 'new content', FILE_APPEND | LOCK_EX);

        self::assertEquals(11, $result);

        /** @var FileNode $node */
        $node = $subject->node('filename');

        self::assertEquals('contentnew content', $node->content);
    }

    /**
     * When LOCK_EX is set the stream wrapper is not called
     */
    public function testShouldWriteToFileWithLock(): void
    {
        $subject = $this->buildSubject();

        $error = '';

        $errorReporting = error_reporting(E_ALL);
        /** @psalm-suppress InvalidArgument */
        set_error_handler(function (int $arg1, string $arg2) use (&$error) {
            $error = $arg2;
        }, E_WARNING);

        file_put_contents($subject->path('filename'), 'new content', LOCK_EX);

        self::assertStringContainsString('file_put_contents(): Exclusive locks may only be set for regular files', $error);

        error_reporting($errorReporting);
        restore_error_handler();
    }

    public function testShouldReturnFalseWhenWritingWithoutAccess(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->permissions = 0000;

        $result = file_put_contents($subject->path('filename'), 'new content');

        self::assertFalse($result);

        /** @var FileNode $node */
        $node = $subject->node('filename');

        self::assertEquals('content', $node->content);
    }

    public function testShouldReturnFalseWhenWritingToDirectory(): void
    {
        $subject = $this->buildSubject();

        $result = file_put_contents($subject->path('directory'), 'new content');

        self::assertFalse($result);
        self::assertInstanceOf(DirectoryNode::class, $subject->node('directory'));
    }

    public function testShouldReturnFalseWhenWritingToSymlink(): void
    {
        $subject = $this->buildSubject();

        $result = file_put_contents($subject->path('symlink'), 'new content');

        self::assertEquals(11, $result);

        /** @var FileNode $node */
        $node = $subject->node('filename');

        self::assertEquals('new content', $node->content);
    }

    public function testShouldUpdateMTime(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->mtime = time() - 30;

        file_put_contents($subject->path('filename'), 'new content');

        self::assertGreaterThanOrEqual(time() - 1, $subject->node('filename')->mtime);
    }

    public function testShouldUpdateCTime(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->ctime = time() - 30;

        file_put_contents($subject->path('filename'), 'new content');

        self::assertGreaterThanOrEqual(time() - 1, $subject->node('filename')->ctime);
    }

    public function testShouldUpdateATime(): void
    {
        $subject = $this->buildSubject();

        $subject->node('filename')->atime = time() - 30;

        file_put_contents($subject->path('filename'), 'new content');

        self::assertGreaterThanOrEqual(time() - 1, $subject->node('filename')->atime);
    }
}

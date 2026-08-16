<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

use function fclose;
use function flock;
use function fopen;

final class FlockTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
        ]);
    }

    public function testShouldAcquireAndReleaseExclusiveFileLock(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp = fopen($file, 'r');

        self::assertIsResource($fp);

        $result = flock($fp, LOCK_EX | LOCK_NB);

        self::assertTrue($result);

        $result = flock($fp, LOCK_UN);

        self::assertTrue($result);
    }

    public function testShouldAcquireAndReleaseSharedFileLock(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp = fopen($file, 'r');

        self::assertIsResource($fp);

        $result = flock($fp, LOCK_SH | LOCK_NB);

        self::assertTrue($result);

        $result = flock($fp, LOCK_UN);

        self::assertTrue($result);
    }

    public function testShouldReleaseLockAfterClosingFileStream(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp1 = fopen($file, 'w');

        self::assertIsResource($fp1);

        $result = flock($fp1, LOCK_EX);

        self::assertTrue($result);

        $fp2 = fopen($file, 'r');

        self::assertIsResource($fp2);

        $result = flock($fp2, LOCK_EX | LOCK_NB);

        self::assertFalse($result);

        $result = fclose($fp1);

        self::assertTrue($result);

        $result = flock($fp2, LOCK_EX | LOCK_NB);

        self::assertTrue($result);
    }

    public function testShouldAllowAccessAfterReleasingFileLock(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp1 = fopen($file, 'w');

        self::assertIsResource($fp1);

        $result = flock($fp1, LOCK_EX);

        self::assertTrue($result);

        $fp2 = fopen($file, 'r');

        self::assertIsResource($fp2);

        $result = flock($fp2, LOCK_EX | LOCK_NB);

        self::assertFalse($result);

        $result = flock($fp1, LOCK_UN);

        self::assertTrue($result);

        $result = flock($fp2, LOCK_EX | LOCK_NB);

        self::assertTrue($result);
    }

    public function testShouldAcquireExclusiveFileLockAndPreventLockingFileForReading(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp1 = fopen($file, 'w');

        self::assertIsResource($fp1);

        $result = flock($fp1, LOCK_EX);

        self::assertTrue($result);

        $fp2 = fopen($file, 'r');

        self::assertIsResource($fp2);

        $result = flock($fp2, LOCK_EX | LOCK_NB);

        self::assertFalse($result);
    }

    public function testShouldAcquireExclusiveFileLockAndPreventLockingFileForWriting(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp1 = fopen($file, 'w');

        self::assertIsResource($fp1);

        $result = flock($fp1, LOCK_EX);

        self::assertTrue($result);

        $fp2 = fopen($file, 'w');

        self::assertIsResource($fp2);

        $result = flock($fp2, LOCK_EX | LOCK_NB);

        self::assertFalse($result);
    }

    public function testShouldAcquireSharedFileLockButAllowSharedLockForSameFile(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp1 = fopen($file, 'r');

        self::assertIsResource($fp1);

        $result = flock($fp1, LOCK_SH);

        self::assertTrue($result);

        $fp2 = fopen($file, 'r');

        self::assertIsResource($fp2);

        $result = flock($fp2, LOCK_SH | LOCK_NB);

        self::assertTrue($result);
    }

    public function testShouldAcquireSharedFileLockAndPreventLockingFileForWriting(): void
    {
        $subject = $this->buildSubject();

        $file = $subject->path('filename');

        $fp1 = fopen($file, 'r');

        self::assertIsResource($fp1);

        $result = flock($fp1, LOCK_SH);

        self::assertTrue($result);

        $fp2 = fopen($file, 'w');

        self::assertIsResource($fp2);

        $result = flock($fp2, LOCK_SH | LOCK_NB);

        self::assertFalse($result);
    }
}

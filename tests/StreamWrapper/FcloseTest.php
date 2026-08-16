<?php

declare(strict_types=1);

namespace Aedon\VFSTest\StreamWrapper;

use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;
use TypeError;

use function fclose;
use function fgets;
use function fopen;

final class FcloseTest extends TestCase
{
    private function buildSubject(): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'filename' => 'content',
        ]);
    }

    public function testShouldCloseFileStream(): void
    {
        $subject = $this->buildSubject();

        $fp = fopen($subject->path('filename'), 'r');

        self::assertIsResource($fp);

        $result = fclose($fp);

        self::assertTrue($result);
    }

    public function testShouldThrowExceptionWhenReadingFileStreamAfterClosingFileStream(): void
    {
        $subject = $this->buildSubject();

        $fp = fopen($subject->path('filename'), 'r');

        self::assertIsResource($fp);

        $result = fclose($fp);

        self::assertTrue($result);

        $this->expectException(TypeError::class);

        /** @psalm-suppress InvalidArgument */
        fgets($fp);
    }
}

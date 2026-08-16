<?php

declare(strict_types=1);

namespace Aedon\VFSTest;

use Aedon\VFS\DirectoryNode;
use Aedon\VFS\FileNode;
use Aedon\VFS\Node;
use Aedon\VFS\Permissions;
use Aedon\VFS\SymlinkNode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PermissionsTest extends TestCase
{
    static public function provideCheckCanReadFileData(): array
    {
        return [
            // User
            'user 0700' => [
                new FileNode('filename', 'filename', 0700, 1000, 2000, ''),
                true,
            ],
            'user 0600' => [
                new FileNode('filename', 'filename', 0600, 1000, 2000, ''),
                true,
            ],
            'user 0500' => [
                new FileNode('filename', 'filename', 0500, 1000, 2000, ''),
                true,
            ],
            'user 0400' => [
                new FileNode('filename', 'filename', 0400, 1000, 2000, ''),
                true,
            ],
            'user 0300' => [
                new FileNode('filename', 'filename', 0300, 1000, 2000, ''),
                false,
            ],
            'user 0200' => [
                new FileNode('filename', 'filename', 0200, 1000, 2000, ''),
                false,
            ],
            'user 0100' => [
                new FileNode('filename', 'filename', 0100, 1000, 2000, ''),
                false,
            ],
            'user 0000' => [
                new FileNode('filename', 'filename', 0000, 1000, 2000, ''),
                false,
            ],
            // Group
            'group 0070' => [
                new FileNode('filename', 'filename', 0070, 2000, 1000, ''),
                true,
            ],
            'group 0060' => [
                new FileNode('filename', 'filename', 0060, 2000, 1000, ''),
                true,
            ],
            'group 0050' => [
                new FileNode('filename', 'filename', 0050, 2000, 1000, ''),
                true,
            ],
            'group 0040' => [
                new FileNode('filename', 'filename', 0040, 2000, 1000, ''),
                true,
            ],
            'group 0030' => [
                new FileNode('filename', 'filename', 0030, 2000, 1000, ''),
                false,
            ],
            'group 0020' => [
                new FileNode('filename', 'filename', 0020, 2000, 1000, ''),
                false,
            ],
            'group 0010' => [
                new FileNode('filename', 'filename', 0010, 2000, 1000, ''),
                false,
            ],
            'group 0000' => [
                new FileNode('filename', 'filename', 0000, 2000, 1000, ''),
                false,
            ],
            // Other
            'other 0007' => [
                new FileNode('filename', 'filename', 0007, 2000, 2000, ''),
                true,
            ],
            'other 0006' => [
                new FileNode('filename', 'filename', 0006, 2000, 2000, ''),
                true,
            ],
            'other 0005' => [
                new FileNode('filename', 'filename', 0005, 2000, 2000, ''),
                true,
            ],
            'other 0004' => [
                new FileNode('filename', 'filename', 0004, 2000, 2000, ''),
                true,
            ],
            'other 0003' => [
                new FileNode('filename', 'filename', 0003, 2000, 2000, ''),
                false,
            ],
            'other 0002' => [
                new FileNode('filename', 'filename', 0002, 2000, 2000, ''),
                false,
            ],
            'other 0001' => [
                new FileNode('filename', 'filename', 0001, 2000, 2000, ''),
                false,
            ],
            'other 0000' => [
                new FileNode('filename', 'filename', 0000, 2000, 2000, ''),
                false,
            ],
            // Examples
            [
                new FileNode('filename', 'filename', 0644, 1000, 1000, ''),
                true,
            ],
            [
                new FileNode('filename', 'filename', 0644, 2000, 1000, ''),
                true,
            ],
            [
                new FileNode('filename', 'filename', 0644, 2000, 2000, ''),
                true,
            ],
            [
                new FileNode('filename', 'filename', 0755, 1000, 1000, ''),
                true,
            ],
            [
                new FileNode('filename', 'filename', 0755, 2000, 1000, ''),
                true,
            ],
            [
                new FileNode('filename', 'filename', 0755, 2000, 2000, ''),
                true,
            ],
        ];
    }

    #[DataProvider('provideCheckCanReadFileData')]
    public function testShouldCheckCanReadFile(Node $node, bool $expectedResult): void
    {
        self::assertEquals($expectedResult, new Permissions()->canRead($node));
    }

    static public function provideCheckCanReadDirectoryData(): array
    {
        return [
            // User
            'user 0700' => [
                new DirectoryNode('directory', 'directory', 0700, 1000, 2000, []),
                true,
            ],
            'user 0600' => [
                new DirectoryNode('directory', 'directory', 0600, 1000, 2000, []),
                false,
            ],
            'user 0500' => [
                new DirectoryNode('directory', 'directory', 0500, 1000, 2000, []),
                true,
            ],
            'user 0400' => [
                new DirectoryNode('directory', 'directory', 0400, 1000, 2000, []),
                false,
            ],
            'user 0300' => [
                new DirectoryNode('directory', 'directory', 0300, 1000, 2000, []),
                false,
            ],
            'user 0200' => [
                new DirectoryNode('directory', 'directory', 0200, 1000, 2000, []),
                false,
            ],
            'user 0100' => [
                new DirectoryNode('directory', 'directory', 0100, 1000, 2000, []),
                false,
            ],
            'user 0000' => [
                new DirectoryNode('directory', 'directory', 0000, 1000, 2000, []),
                false,
            ],
            // Group
            'group 0070' => [
                new DirectoryNode('directory', 'directory', 0070, 2000, 1000, []),
                true,
            ],
            'group 0060' => [
                new DirectoryNode('directory', 'directory', 0060, 2000, 1000, []),
                false,
            ],
            'group 0050' => [
                new DirectoryNode('directory', 'directory', 0050, 2000, 1000, []),
                true,
            ],
            'group 0040' => [
                new DirectoryNode('directory', 'directory', 0040, 2000, 1000, []),
                false,
            ],
            'group 0030' => [
                new DirectoryNode('directory', 'directory', 0030, 2000, 1000, []),
                false,
            ],
            'group 0020' => [
                new DirectoryNode('directory', 'directory', 0020, 2000, 1000, []),
                false,
            ],
            'group 0010' => [
                new DirectoryNode('directory', 'directory', 0010, 2000, 1000, []),
                false,
            ],
            'group 0000' => [
                new DirectoryNode('directory', 'directory', 0000, 2000, 1000, []),
                false,
            ],
            // Other
            'other 0007' => [
                new DirectoryNode('directory', 'directory', 0007, 2000, 2000, []),
                true,
            ],
            'other 0006' => [
                new DirectoryNode('directory', 'directory', 0006, 2000, 2000, []),
                false,
            ],
            'other 0005' => [
                new DirectoryNode('directory', 'directory', 0005, 2000, 2000, []),
                true,
            ],
            'other 0004' => [
                new DirectoryNode('directory', 'directory', 0004, 2000, 2000, []),
                false,
            ],
            'other 0003' => [
                new DirectoryNode('directory', 'directory', 0003, 2000, 2000, []),
                false,
            ],
            'other 0002' => [
                new DirectoryNode('directory', 'directory', 0002, 2000, 2000, []),
                false,
            ],
            'other 0001' => [
                new DirectoryNode('directory', 'directory', 0001, 2000, 2000, []),
                false,
            ],
            'other 0000' => [
                new DirectoryNode('directory', 'directory', 0000, 2000, 2000, []),
                false,
            ],
            // Examples
            [
                new DirectoryNode('directory', 'directory', 0644, 1000, 1000, []),
                false,
            ],
            [
                new DirectoryNode('directory', 'directory', 0644, 2000, 1000, []),
                false,
            ],
            [
                new DirectoryNode('directory', 'directory', 0655, 2000, 2000, []),
                true,
            ],
            [
                new DirectoryNode('directory', 'directory', 0755, 1000, 1000, []),
                true,
            ],
            [
                new DirectoryNode('directory', 'directory', 0755, 2000, 1000, []),
                true,
            ],
            [
                new DirectoryNode('directory', 'directory', 0755, 2000, 2000, []),
                true,
            ],
        ];
    }

    #[DataProvider('provideCheckCanReadDirectoryData')]
    public function testShouldCheckCanReadDirectory(Node $node, bool $expectedResult): void
    {
        self::assertEquals($expectedResult, new Permissions()->canRead($node));
    }

    static public function provideCheckCanWriteFileData(): array
    {
        return [
            // User
            'user 0700' => [
                new FileNode('filename', 'filename', 0700, 1000, 2000, ''),
                true,
            ],
            'user 0600' => [
                new FileNode('filename', 'filename', 0600, 1000, 2000, ''),
                true,
            ],
            'user 0500' => [
                new FileNode('filename', 'filename', 0500, 1000, 2000, ''),
                false,
            ],
            'user 0400' => [
                new FileNode('filename', 'filename', 0400, 1000, 2000, ''),
                false,
            ],
            'user 0300' => [
                new FileNode('filename', 'filename', 0300, 1000, 2000, ''),
                true,
            ],
            'user 0200' => [
                new FileNode('filename', 'filename', 0200, 1000, 2000, ''),
                true,
            ],
            'user 0100' => [
                new FileNode('filename', 'filename', 0100, 1000, 2000, ''),
                false,
            ],
            'user 0000' => [
                new FileNode('filename', 'filename', 0000, 1000, 2000, ''),
                false,
            ],
            // Group
            'group 0070' => [
                new FileNode('filename', 'filename', 0070, 2000, 1000, ''),
                true,
            ],
            'group 0060' => [
                new FileNode('filename', 'filename', 0060, 2000, 1000, ''),
                true,
            ],
            'group 0050' => [
                new FileNode('filename', 'filename', 0050, 2000, 1000, ''),
                false,
            ],
            'group 0040' => [
                new FileNode('filename', 'filename', 0040, 2000, 1000, ''),
                false,
            ],
            'group 0030' => [
                new FileNode('filename', 'filename', 0030, 2000, 1000, ''),
                true,
            ],
            'group 0020' => [
                new FileNode('filename', 'filename', 0020, 2000, 1000, ''),
                true,
            ],
            'group 0010' => [
                new FileNode('filename', 'filename', 0010, 2000, 1000, ''),
                false,
            ],
            'group 0000' => [
                new FileNode('filename', 'filename', 0000, 2000, 1000, ''),
                false,
            ],
            // Other
            'other 0007' => [
                new FileNode('filename', 'filename', 0007, 2000, 2000, ''),
                true,
            ],
            'other 0006' => [
                new FileNode('filename', 'filename', 0006, 2000, 2000, ''),
                true,
            ],
            'other 0005' => [
                new FileNode('filename', 'filename', 0005, 2000, 2000, ''),
                false,
            ],
            'other 0004' => [
                new FileNode('filename', 'filename', 0004, 2000, 2000, ''),
                false,
            ],
            'other 0003' => [
                new FileNode('filename', 'filename', 0003, 2000, 2000, ''),
                true,
            ],
            'other 0002' => [
                new FileNode('filename', 'filename', 0002, 2000, 2000, ''),
                true,
            ],
            'other 0001' => [
                new FileNode('filename', 'filename', 0001, 2000, 2000, ''),
                false,
            ],
            'other 0000' => [
                new FileNode('filename', 'filename', 0000, 2000, 2000, ''),
                false,
            ],
            // Examples
            [
                new FileNode('filename', 'filename', 0655, 1000, 1000, ''),
                true,
            ],
            [
                new FileNode('filename', 'filename', 0644, 2000, 1000, ''),
                false,
            ],
            [
                new FileNode('filename', 'filename', 0644, 2000, 2000, ''),
                false,
            ],
            [
                new FileNode('filename', 'filename', 0755, 1000, 1000, ''),
                true,
            ],
            [
                new FileNode('filename', 'filename', 0733, 2000, 1000, ''),
                true,
            ],
            [
                new FileNode('filename', 'filename', 0722, 2000, 2000, ''),
                true,
            ],
        ];
    }

    #[DataProvider('provideCheckCanWriteFileData')]
    public function testShouldCheckCanWrite(Node $node, bool $expectedResult): void
    {
        self::assertEquals($expectedResult, new Permissions()->canWrite($node));
    }

    static public function provideCheckCanWriteDirectoryData(): array
    {
        return [
            // User
            'user 0700' => [
                new DirectoryNode('directory', 'directory', 0700, 1000, 2000, []),
                true,
            ],
            'user 0600' => [
                new DirectoryNode('directory', 'directory', 0600, 1000, 2000, []),
                false,
            ],
            'user 0500' => [
                new DirectoryNode('directory', 'directory', 0500, 1000, 2000, []),
                false,
            ],
            'user 0400' => [
                new DirectoryNode('directory', 'directory', 0400, 1000, 2000, []),
                false,
            ],
            'user 0300' => [
                new DirectoryNode('directory', 'directory', 0300, 1000, 2000, []),
                true,
            ],
            'user 0200' => [
                new DirectoryNode('directory', 'directory', 0200, 1000, 2000, []),
                false,
            ],
            'user 0100' => [
                new DirectoryNode('directory', 'directory', 0100, 1000, 2000, []),
                false,
            ],
            'user 0000' => [
                new DirectoryNode('directory', 'directory', 0000, 1000, 2000, []),
                false,
            ],
            // Group
            'group 0070' => [
                new DirectoryNode('directory', 'directory', 0070, 2000, 1000, []),
                true,
            ],
            'group 0060' => [
                new DirectoryNode('directory', 'directory', 0060, 2000, 1000, []),
                false,
            ],
            'group 0050' => [
                new DirectoryNode('directory', 'directory', 0050, 2000, 1000, []),
                false,
            ],
            'group 0040' => [
                new DirectoryNode('directory', 'directory', 0040, 2000, 1000, []),
                false,
            ],
            'group 0030' => [
                new DirectoryNode('directory', 'directory', 0030, 2000, 1000, []),
                true,
            ],
            'group 0020' => [
                new DirectoryNode('directory', 'directory', 0020, 2000, 1000, []),
                false,
            ],
            'group 0010' => [
                new DirectoryNode('directory', 'directory', 0010, 2000, 1000, []),
                false,
            ],
            'group 0000' => [
                new DirectoryNode('directory', 'directory', 0000, 2000, 1000, []),
                false,
            ],
            // Other
            'other 0007' => [
                new DirectoryNode('directory', 'directory', 0007, 2000, 2000, []),
                true,
            ],
            'other 0006' => [
                new DirectoryNode('directory', 'directory', 0006, 2000, 2000, []),
                false,
            ],
            'other 0005' => [
                new DirectoryNode('directory', 'directory', 0005, 2000, 2000, []),
                false,
            ],
            'other 0004' => [
                new DirectoryNode('directory', 'directory', 0004, 2000, 2000, []),
                false,
            ],
            'other 0003' => [
                new DirectoryNode('directory', 'directory', 0003, 2000, 2000, []),
                true,
            ],
            'other 0002' => [
                new DirectoryNode('directory', 'directory', 0002, 2000, 2000, []),
                false,
            ],
            'other 0001' => [
                new DirectoryNode('directory', 'directory', 0001, 2000, 2000, []),
                false,
            ],
            'other 0000' => [
                new DirectoryNode('directory', 'directory', 0000, 2000, 2000, []),
                false,
            ],
            // Examples
            [
                new DirectoryNode('directory', 'directory', 0644, 1000, 1000, []),
                false,
            ],
            [
                new DirectoryNode('directory', 'directory', 0644, 2000, 1000, []),
                false,
            ],
            [
                new DirectoryNode('directory', 'directory', 0777, 2000, 2000, []),
                true,
            ],
            [
                new DirectoryNode('directory', 'directory', 0733, 1000, 1000, []),
                true,
            ],
            [
                new DirectoryNode('directory', 'directory', 0722, 2000, 1000, []),
                false,
            ],
            [
                new DirectoryNode('directory', 'directory', 0755, 2000, 2000, []),
                false,
            ],
        ];
    }

    #[DataProvider('provideCheckCanWriteDirectoryData')]
    public function testShouldCheckCanWriteDirectory(Node $node, bool $expectedResult): void
    {
        self::assertEquals($expectedResult, new Permissions()->canWrite($node));
    }

    static public function provideGetModeData(): array
    {
        return [
            [new FileNode('', '', 0755, 1000, 1000, ''), 33261],
            [new FileNode('', '', 0000, 1000, 1000, ''), 32768],
            [new DirectoryNode('', '', 0755, 1000, 1000, []), 16877],
            [new DirectoryNode('', '', 0000, 1000, 1000, []), 16384],
            [new SymlinkNode('', '', 0755, 1000, 1000, ''), 41453],
            [new SymlinkNode('', '', 0000, 1000, 1000, ''), 40960],
        ];
    }

    #[DataProvider('provideGetModeData')]
    public function testShouldGetMode(Node $node, int $expectedResult): void
    {
        self::assertEquals($expectedResult, new Permissions()->getMode($node));
    }
}

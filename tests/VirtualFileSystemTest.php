<?php

declare(strict_types=1);

namespace Aedon\VFSTest;

use Aedon\VFS\DirectoryNode;
use Aedon\VFS\EmptyNode;
use Aedon\VFS\FileNode;
use Aedon\VFS\SymlinkNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

final class VirtualFileSystemTest extends TestCase
{
    private function buildSubject(int $permissions = VirtualFileSystem::DEFAULT_PERMISSIONS): VirtualFileSystem
    {
        return VirtualFileSystem::create([
            'directoryA' => [
                'filename' => 'directoryAfilenameContent',
            ],
            'directoryB' => [
                'directoryC' => [
                    'filename' => 'directoryBdirectoryCfilenameContent',
                ],
            ],
            'filename' => 'filenameContent',
            'symlink' => '@directoryB/directoryC/filename',
        ], $permissions);
    }

    public function testShouldCreateVirtualFileSystem(): void
    {
        $subject = $this->buildSubject();

        self::assertInstanceOf(DirectoryNode::class, $subject->node('directoryA'));
        self::assertInstanceOf(FileNode::class, $subject->node('directoryA/filename'));
        self::assertInstanceOf(DirectoryNode::class, $subject->node('directoryB'));
        self::assertInstanceOf(DirectoryNode::class, $subject->node('directoryB/directoryC'));
        self::assertInstanceOf(FileNode::class, $subject->node('directoryB/directoryC/filename'));
        self::assertInstanceOf(FileNode::class, $subject->node('filename'));
        self::assertInstanceOf(SymlinkNode::class, $subject->node('symlink'));

        self::assertInstanceOf(EmptyNode::class, $subject->node('z'));
        self::assertInstanceOf(EmptyNode::class, $subject->node('z/y'));
        self::assertEquals('z', $subject->node('z')->filename);
        self::assertEquals('y', $subject->node('z/y')->filename);

        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH . '/directoryA', $subject->path('directoryA'));
        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH . '/directoryA/filename', $subject->path('directoryA/filename'));
        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH . '/directoryB', $subject->path('directoryB'));
        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH . '/directoryB/directoryC', $subject->path('directoryB/directoryC'));
        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH . '/directoryB/directoryC/filename', $subject->path('directoryB/directoryC/filename'));
        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH . '/filename', $subject->path('filename'));
        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH . '/symlink', $subject->path('symlink'));

        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH . '/z', $subject->path('z'));
    }

    public function testShouldResolveNodeWithProtocolInPath(): void
    {
        $subject = $this->buildSubject();

        self::assertInstanceOf(FileNode::class, $subject->node(VirtualFileSystem::PROTOCOL_PATH . '/filename'));
    }

    public function testShouldGetNodes(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals([
            'directoryA' => new DirectoryNode('directoryA', 'directoryA', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, [
                'directoryA/filename' => new FileNode('filename', 'directoryA/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'directoryAfilenameContent'),
            ]),
            'directoryA/filename' => new FileNode('filename', 'directoryA/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'directoryAfilenameContent'),
            'directoryB' => new DirectoryNode('directoryB', 'directoryB', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, [
                'directoryB/directoryC' => new DirectoryNode('directoryC', 'directoryB/directoryC', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, [
                    'directoryB/directoryC/filename' => new FileNode('filename', 'directoryB/directoryC/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'directoryBdirectoryCfilenameContent'),
                ]),
            ]),
            'directoryB/directoryC' => new DirectoryNode('directoryC', 'directoryB/directoryC', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, [
                'directoryB/directoryC/filename' => new FileNode('filename', 'directoryB/directoryC/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'directoryBdirectoryCfilenameContent'),
            ]),
            'directoryB/directoryC/filename' => new FileNode('filename', 'directoryB/directoryC/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'directoryBdirectoryCfilenameContent'),
            'filename' => new FileNode('filename', 'filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'filenameContent'),
            'symlink' => new SymlinkNode('symlink', 'symlink', VirtualFileSystem::DEFAULT_SYMLINK_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, '@directoryB/directoryC/filename', new FileNode('filename', 'directoryB/directoryC/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'directoryBdirectoryCfilenameContent')),
        ], $subject->getNodes());
    }

    public function testShouldGetDefaultPermissions(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals(VirtualFileSystem::DEFAULT_PERMISSIONS, $subject->node('directoryA')->permissions);
    }

    public function testShouldUseCustomPermissions(): void
    {
        $subject = $this->buildSubject(0755);

        self::assertEquals(0755, $subject->node('directoryA')->permissions);
    }

    public function testShouldUseDefaultUserId(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals(VirtualFileSystem::DEFAULT_USER_ID, $subject->node('directoryA')->userId);
    }

    public function testShouldUseDefaultGroupId(): void
    {
        $subject = $this->buildSubject();

        self::assertEquals(VirtualFileSystem::DEFAULT_GROUP_ID, $subject->node('directoryA')->groupId);
    }

    public function testShouldAddFile(): void
    {
        $subject = $this->buildSubject();

        $subject->addFile('directoryA/anotherfile', 'anotherfilecontent');

        /** @var FileNode $node */
        $node = $subject->node('directoryA/anotherfile');

        self::assertEquals('anotherfilecontent', $node->content);

        /** @var DirectoryNode $node */
        $node = $subject->node('directoryA');

        self::assertInstanceOf(FileNode::class, $node->children['directoryA/anotherfile']);
    }

    public function testShouldAddEmptyDirectory(): void
    {
        $subject = $this->buildSubject();

        $subject->addDirectory('directoryA/newDirectory');

        self::assertInstanceOf(DirectoryNode::class, $subject->node('directoryA/newDirectory'));

        /** @var DirectoryNode $node */
        $node = $subject->node('directoryA/newDirectory');

        self::assertEquals([], $node->children);
    }

    public function testShouldAddDirectoryWithFile(): void
    {
        $subject = $this->buildSubject();

        $subject->addDirectory('directoryA/newDirectory', [
            'filename' => 'filecontent',
            'directory' => [
                'filename' => 'morefilecontent',
            ],
        ]);

        self::assertInstanceOf(DirectoryNode::class, $subject->node('directoryA/newDirectory'));
        self::assertInstanceOf(FileNode::class, $subject->node('directoryA/newDirectory/filename'));
        self::assertInstanceOf(DirectoryNode::class, $subject->node('directoryA/newDirectory/directory'));
        self::assertInstanceOf(FileNode::class, $subject->node('directoryA/newDirectory/directory/filename'));

        /** @var FileNode $node */
        $node = $subject->node('directoryA/newDirectory/filename');

        self::assertEquals('filecontent', $node->content);

        /** @var FileNode $node */
        $node = $subject->node('directoryA/newDirectory/directory/filename');

        self::assertEquals('morefilecontent', $node->content);

        /** @var DirectoryNode $node */
        $node = $subject->node('directoryA/newDirectory');

        self::assertCount(2, $node->children);

        /** @var DirectoryNode $node */
        $node = $subject->node('directoryA/newDirectory/directory');

        self::assertCount(1, $node->children);

        $nodes = $subject->getNodes();

        self::assertArrayHasKey('directoryA/newDirectory', $nodes);
        self::assertArrayHasKey('directoryA/newDirectory/filename', $nodes);
        self::assertArrayHasKey('directoryA/newDirectory/directory', $nodes);
        self::assertArrayHasKey('directoryA/newDirectory/directory/filename', $nodes);
    }

    public function testShouldAddSymlink(): void
    {
        $subject = $this->buildSubject();

        $subject->addSymlink('directoryA/link', 'directoryA/filename');

        self::assertInstanceOf(SymlinkNode::class, $subject->node('directoryA/link'));

        /** @var DirectoryNode $node */
        $node = $subject->node('directoryA');

        self::assertCount(2, $node->children);
    }

    public function testShouldRemoveNode(): void
    {
        $subject = $this->buildSubject();

        $subject->removeNode('directoryA/filename');

        self::assertInstanceOf(EmptyNode::class, $subject->node('directoryA/filename'));

        /** @var DirectoryNode $node */
        $node = $subject->node('directoryA');

        self::assertCount(0, $node->children);
    }

    public function testShouldRemoveNodeRecursive(): void
    {
        $subject = $this->buildSubject();

        $subject->removeNode('directoryB');

        self::assertInstanceOf(EmptyNode::class, $subject->node('directoryB'));
        self::assertInstanceOf(EmptyNode::class, $subject->node('directoryB/directoryC'));
        self::assertInstanceOf(EmptyNode::class, $subject->node('directoryB/directoryC/filename'));
    }

    public function testShouldGetDirectory(): void
    {
        $subject = $this->buildSubject();

        self::assertNull($subject->directory('directoryA'));
        self::assertNull($subject->directory('filename'));

        self::assertInstanceOf(DirectoryNode::class, $subject->directory('directoryB/directoryC/filename'));

        /** @var DirectoryNode $node */
        $node = $subject->directory('directoryB/directoryC/filename');

        self::assertEquals('directoryB/directoryC', $node->path);
    }
}

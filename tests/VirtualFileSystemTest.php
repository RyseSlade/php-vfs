<?php

declare(strict_types=1);

namespace Aedon\VFSTest;

use Aedon\VFS\DirectoryNode;
use Aedon\VFS\EmptyNode;
use Aedon\VFS\FileNode;
use Aedon\VFS\SymlinkNode;
use Aedon\VFS\VirtualFileSystem;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

        self::assertInstanceOf(DirectoryNode::class, $subject->node('/'));
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

        self::assertEquals(VirtualFileSystem::PROTOCOL_PATH . '/', $subject->path('/'));
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
            new FileNode('filename', 'directoryA/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'directoryAfilenameContent'),
            new DirectoryNode('directoryA', 'directoryA', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, [
                new FileNode('filename', 'directoryA/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'directoryAfilenameContent'),
            ]),
            new FileNode('filename', 'directoryB/directoryC/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'directoryBdirectoryCfilenameContent'),
            new DirectoryNode('directoryC', 'directoryB/directoryC', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, [
                new FileNode('filename', 'directoryB/directoryC/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'directoryBdirectoryCfilenameContent'),
            ]),
            new DirectoryNode('directoryB', 'directoryB', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, [
                new DirectoryNode('directoryC', 'directoryB/directoryC', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, [
                    new FileNode('filename', 'directoryB/directoryC/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'directoryBdirectoryCfilenameContent'),
                ]),
            ]),
            new FileNode('filename', 'filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'filenameContent'),
            new SymlinkNode('symlink', 'symlink', VirtualFileSystem::DEFAULT_SYMLINK_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, '@directoryB/directoryC/filename', new FileNode('filename', 'directoryB/directoryC/filename', VirtualFileSystem::DEFAULT_PERMISSIONS, VirtualFileSystem::DEFAULT_USER_ID, VirtualFileSystem::DEFAULT_GROUP_ID, 'directoryBdirectoryCfilenameContent')),
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
    }

    public function testShouldAddFileToRoot(): void
    {
        $subject = $this->buildSubject();

        $subject->addFile('newfile', 'newfilecontent');

        /** @var FileNode $node */
        $node = $subject->node('newfile');

        self::assertInstanceOf(FileNode::class, $node);
        self::assertEquals('newfilecontent', $node->content);
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

    public function testShouldAddDirectoryToRoot(): void
    {
        $subject = $this->buildSubject();

        $subject->addDirectory('newDirectory');

        self::assertInstanceOf(DirectoryNode::class, $subject->node('newDirectory'));

        /** @var DirectoryNode $node */
        $node = $subject->node('newDirectory');

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

    public function testShouldAddSymlinkToRoot(): void
    {
        $subject = $this->buildSubject();

        $subject->addSymlink('newlink', 'directoryA/filename');

        self::assertInstanceOf(SymlinkNode::class, $subject->node('newlink'));

        /** @var SymlinkNode $node */
        $node = $subject->node('newlink');

        self::assertEquals('directoryA/filename', $node->target);
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

    public function testShouldThrowExceptionWhenRemovingRootNode(): void
    {
        $subject = $this->buildSubject();

        $this->expectException(RuntimeException::class);

        $subject->removeNode('/');
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

        self::assertInstanceOf(DirectoryNode::class, $subject->directory('directoryA'));
        self::assertInstanceOf(DirectoryNode::class, $subject->directory('filename'));
        self::assertInstanceOf(DirectoryNode::class, $subject->directory('directoryB/directoryC/filename'));

        /** @var DirectoryNode $node */
        $node = $subject->directory('directoryB/directoryC/filename');

        self::assertEquals('directoryB/directoryC', $node->path);
    }

    public function testShouldRenameFile(): void
    {
        $subject = $this->buildSubject();

        $subject->renameNode('directoryA/filename', 'directoryA/newfilename');

        self::assertInstanceOf(EmptyNode::class, $subject->node('directoryA/filename'));
        self::assertInstanceOf(FileNode::class, $subject->node('directoryA/newfilename'));

        /** @var DirectoryNode $directory */
        $directory = $subject->directory('directoryA/newfilename');

        self::assertCount(1, $directory->children);
        self::assertInstanceOf(FileNode::class, $directory->children[0]);
    }

    public function testShouldRenameSymlink(): void
    {
        $subject = $this->buildSubject();

        $subject->renameNode('symlink', 'newsymlink');

        self::assertInstanceOf(EmptyNode::class, $subject->node('symlink'));
        self::assertInstanceOf(SymlinkNode::class, $subject->node('newsymlink'));

        /** @var DirectoryNode $directory */
        $directory = $subject->directory('newsymlink');

        self::assertCount(4, $directory->children);
        self::assertInstanceOf(SymlinkNode::class, $directory->children[3]);
    }

    public function testShouldRenameDirectory(): void
    {
        $subject = $this->buildSubject();

        $subject->renameNode('directoryA', 'directoryAnew');

        self::assertInstanceOf(EmptyNode::class, $subject->node('directoryA'));
        self::assertInstanceOf(DirectoryNode::class, $subject->node('directoryAnew'));

        /** @var DirectoryNode $directory */
        $directory = $subject->directory('directoryAnew');

        self::assertCount(4, $directory->children);
        self::assertInstanceOf(DirectoryNode::class, $directory->children[0]);
        self::assertEquals('directoryAnew', $directory->children[0]->path);
    }

    public function testShouldUpdateSymlinksWhenRenamingTargetNode(): void
    {
        $subject = $this->buildSubject();

        $subject->renameNode('directoryB/directoryC/filename', 'directoryB/directoryC/newfilename');

        self::assertInstanceOf(EmptyNode::class, $subject->node('directoryB/directoryC/filename'));
        self::assertInstanceOf(FileNode::class, $subject->node('directoryB/directoryC/newfilename'));

        /** @var SymlinkNode $symlinkNode */
        $symlinkNode = $subject->node('symlink');

        self::assertEquals('@directoryB/directoryC/newfilename', $symlinkNode->target);
        self::assertEquals($subject->node('directoryB/directoryC/newfilename'), $symlinkNode->linkTarget);
    }

    public function testShouldThrowExceptionWhenRenamingRootNode(): void
    {
        $subject = $this->buildSubject();

        $this->expectException(RuntimeException::class);

        $subject->renameNode('/', 'directoryA2');
    }
}
